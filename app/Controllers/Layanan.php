<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Models\LaporanKerjaModel;
use App\Models\LaporanLampiranModel;
use CodeIgniter\I18n\Time;

class Layanan extends BaseController
{
    /* ======================== UTIL KECIL ======================== */

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = str_replace(['–', '—'], '-', $s);
        $s = preg_replace('/[^a-z0-9\s\-]/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('/\-+/', '-', $s);
        return trim($s, '-');
    }

    private function normalizeSpec(?string $spec): array
    {
        if (!$spec)
            return [];
        $parts = preg_split('/[,\n;|\/]+/u', $spec) ?: [];
        $res = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '')
                $res[] = $this->slugify($p);
        }
        return array_values(array_unique($res));
    }

    private function servicesCatalog(): array
    {
        $ppat = [
            'CEK LOKASI',
            'CEK KAWASAN',
            'VALIDASI',
            'ALIH WILAYAH',
            'PEMULIHAN DATA',
            'ROYA',
            'PENGECEKAN SERTIPIKAT',
            'AKTA JUAL BELI',
            'AKTA HIBAH',
            'AKTA PEMBAGIAN HAK BERSAMA',
            'TURUN WARIS',
            'PEMISAHAN',
            'PENINGKATAN HAK',
            'PELEPASAN HAK',
            'TURUN HAK',
            'UBAH LAHAN PERTANIAN JADI LAHAN PEKARANGAN',
            'LAPORAN BULANAN'
        ];
        $notaris = [
            'SKMHT',
            'PENDIRIAN PT',
            'PERUBAHAN PT',
            'PENDIRIAN YAYASAN',
            'PERUBAHAN YAYASAN',
            'PENDIRIAN PERKUMPULAN',
            'PERUBAHAN PERKUMPULAN',
            'PENDIRIAN PERSEROAN KOMANDITER',
            'PERUBAHAN PERSEROAN KOMANDITER',
            'PENDIRIAN KOPERASI',
            'PERUBAHAN KOPERASI',
            'PERJANJIAN JUAL BELI',
            'KUASA UNTUK MENJUAL',
            'PERJANJIAN – PERJANJIAN'
        ];

        $out = [];
        foreach ($ppat as $n) {
            $slug = $this->slugify($n);
            $out[$slug] = ['name' => $n, 'slug' => $slug, 'category' => 'PPAT'];
        }
        foreach ($notaris as $n) {
            $slug = $this->slugify($n);
            $out[$slug] = ['name' => $n, 'slug' => $slug, 'category' => 'Notaris'];
        }
        return $out;
    }

    private function backfillAdminsIfMissing(): void
    {
        $userM = new UserModel();
        $empM = new EmployeeModel();

        $db = \Config\Database::connect();
        $empCols = array_map('strtolower', $db->getFieldNames('employees'));
        $has = fn(string $c) => in_array(strtolower($c), $empCols, true);

        $pick = function (array $row, array $keys, $default = null) {
            foreach ($keys as $k) {
                if (array_key_exists($k, $row)) {
                    $v = is_string($row[$k]) ? trim($row[$k]) : $row[$k];
                    if ($v !== null && $v !== '')
                        return $v;
                }
            }
            return $default;
        };

        $admins = $userM->asArray()->where('role', 'admin')->findAll();

        foreach ($admins as $u) {
            $uid = (int) ($u['id'] ?? 0);
            $name = $pick($u, ['display_name', 'full_name', 'fullname', 'nama', 'name', 'username'], 'Admin #' . $uid);
            $email = $pick($u, ['email', 'user_email', 'mail'], null);
            $foto = $pick($u, ['avatar', 'avatar_url', 'user_image', 'image', 'photo', 'foto'], null);
            $jab = $pick($u, ['jabatan', 'title', 'role'], 'Admin');

            $exists = false;
            if ($has('email') && $email)
                $exists = (bool) $empM->where('email', $email)->first();
            if (!$exists && $has('nama')) {
                $qb = $empM->where('nama', $name);
                if ($has('jabatan'))
                    $qb->where('jabatan', $jab);
                $exists = (bool) $qb->first();
            }
            if (!$exists && $has('nama') && $uid > 0)
                $exists = (bool) $empM->where('nama', 'Admin #' . $uid)->first();

            if ($exists)
                continue;

            $payload = [];
            if ($has('nama'))
                $payload['nama'] = $name;
            if ($has('jabatan'))
                $payload['jabatan'] = $jab;
            if ($has('spesialisasi'))
                $payload['spesialisasi'] = '';
            if ($has('foto'))
                $payload['foto'] = $foto;
            if ($has('email') && $email)
                $payload['email'] = $email;
            if ($has('status'))
                $payload['status'] = 'aktif';

            if ($payload)
                $empM->insert($payload);
        }
    }

    private function isAdmin(): bool
    {
        $s = session();
        $role = strtolower((string) ($s->get('role') ?? $s->get('user.role') ?? ''));
        if ($role === 'admin')
            return true;

        try {
            if (function_exists('logged_in') && logged_in()) {
                $user = auth()->user();
                if ($user) {
                    if (method_exists($user, 'inGroup') && $user->inGroup('admin'))
                        return true;
                    if (isset($user->role) && strtolower((string) $user->role) === 'admin')
                        return true;
                }
            }
        } catch (\Throwable $e) {
        }
        return false;
    }

    private function requireAdminJson()
    {
        if (!$this->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'FORBIDDEN']);
        }
        return null;
    }

    private function jsonOk(array $data = [])
    {
        return $this->response->setJSON(['ok' => true] + $data);
    }

    private function jsonErr(string $msg, int $code = 400)
    {
        return $this->response->setStatusCode($code)->setJSON(['ok' => false, 'error' => $msg]);
    }

    /* ======================== PAGE: LAYANAN ======================== */

    public function index()
    {
        $this->backfillAdminsIfMissing();

        $tz = 'Asia/Jakarta';
        $date = (string) ($this->request->getGet('date') ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = Time::today($tz)->toDateString();
        }

        $serviceParam = (string) ($this->request->getGet('service') ?? '');
        $serviceSlug = $serviceParam !== '' ? $this->slugify($serviceParam) : '';
        $catalog = $this->servicesCatalog();
        $activeService = $serviceSlug && isset($catalog[$serviceSlug]) ? $catalog[$serviceSlug] : null;

        $empM = new EmployeeModel();
        $employees = $empM->asArray()->orderBy('nama', 'ASC')->findAll();

        $db = \Config\Database::connect();
        $empCols = array_map('strtolower', $db->getFieldNames('employees'));
        $hasStatus = in_array('status', $empCols, true);

        if ($hasStatus) {
            $employees = array_values(array_filter($employees, function (array $e) {
                $v = strtolower(trim((string) ($e['status'] ?? '')));
                return in_array($v, ['aktif', 'active', '1', 'ya', 'true', 'yes'], true);
            }));
        }

        if ($activeService) {
            $employees = array_values(array_filter($employees, function (array $e) use ($serviceSlug) {
                $specs = $this->normalizeSpec($e['spesialisasi'] ?? '');
                return in_array($serviceSlug, $specs, true);
            }));
        }

        $isAdmin = $this->isAdmin();

        // ===== Jadwal Hari Ini untuk admin/karyawan =====
        $bookingsToday = [];
        $counts = ['confirmed' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];

        $employeeId = null;
        try {
            $employeeId = \App\Libraries\EmployeeResolver::ensureForCurrentUser();
        } catch (\Throwable $e) {
        }

        if ($employeeId) {
            $todayDate = Time::today($tz)->toDateString();
            $startTs = Time::today($tz)->toDateTimeString();
            $endTs = Time::tomorrow($tz)->toDateTimeString();

            $bookingsToday = $db->table('booking b')
                ->select('b.id AS booking_id, b.id, b.status, b.created_at,
                          u.nama AS user_nama, u.email AS user_email,
                          kj.id AS jadwal_id, kj.tanggal, kj.jam')
                ->join('users u', 'u.id = b.user_id', 'left')
                ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
                ->groupStart()
                ->where('kj.karyawan_id', $employeeId)
                ->orWhere('b.karyawan_id', $employeeId)
                ->groupEnd()
                ->groupStart()
                ->where('DATE(kj.tanggal)', $todayDate)
                ->orGroupStart()
                ->where('kj.tanggal >=', $startTs)
                ->where('kj.tanggal <', $endTs)
                ->groupEnd()
                ->groupEnd()
                ->orderBy('kj.jam', 'ASC')->orderBy('b.created_at', 'ASC')
                ->get()->getResultArray();

            foreach ($bookingsToday as $r) {
                $s = strtolower((string) ($r['status'] ?? ''));
                $s = match ($s) {
                    'approve' => 'approved',
                    'cancel', 'cancelled' => 'canceled',
                    'done' => 'completed',
                    'booked' => 'pending',
                    default => $s,
                };
                if (in_array($s, ['confirmed', 'approved'], true))
                    $counts['confirmed']++;
                elseif ($s === 'pending')
                    $counts['pending']++;
                elseif ($s === 'completed')
                    $counts['completed']++;
                elseif ($s === 'canceled')
                    $counts['cancelled']++;
            }
        }

        return view('layanan', [
            'employees' => $employees,
            'date' => $date,
            'serviceSlug' => $serviceSlug,
            'activeService' => $activeService,
            'catalog' => $catalog,
            'isAdmin' => $isAdmin,
            'bookingsToday' => $bookingsToday,
            'counts' => $counts,
        ]);
    }

    /* =================== API: LAPORAN NOTARIS & PPAT =================== */

    /** GET /admin/laporan/feed?bulan=9&tahun=2025
     *  return: { ok, notaris:[], ppat:[] }
     */
    public function laporanFeed()
    {
        if ($x = $this->requireAdminJson())
            return $x;

        $bulan = (int) ($this->request->getGet('bulan') ?? date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));

        $lapM = new LaporanKerjaModel();
        $rowsNot = $lapM->getMonthly('NOTARIS', $bulan, $tahun);
        $rowsPpat = $lapM->getMonthly('PPAT', $bulan, $tahun);

        $map = function (array $rows): array {
            $i = 1;
            $out = [];
            foreach ($rows as $r) {
                $payload = json_decode((string) ($r['data_json'] ?? '{}'), true) ?: [];
                $out[] = [
                    'id' => (int) $r['id'],
                    'row_no' => $i++,
                    'nomor_bulanan' => (int) ($r['nomor_bulanan'] ?? 0),
                    'tanggal' => (string) ($r['tanggal'] ?? ''),
                    'payload' => $payload,
                    'created_at' => (string) ($r['created_at'] ?? ''),
                    'created_by' => (int) ($r['created_by'] ?? 0),
                ];
            }
            return $out;
        };

        return $this->jsonOk([
            'notaris' => $map($rowsNot),
            'ppat' => $map($rowsPpat),
        ]);
    }

    /** POST /admin/laporan/upload
     *  Body: kat=NOTARIS|PPAT, bulan,tahun,... + lampiran[]
     */
    public function laporanUpload()
    {
        if ($x = $this->requireAdminJson())
            return $x;

        $req = $this->request;
        $kat = strtoupper((string) $req->getPost('kat'));
        $bulan = (int) ($req->getPost('bulan') ?? date('n'));
        $tahun = (int) ($req->getPost('tahun') ?? date('Y'));
        $userId = (int) (session('id') ?? session('user_id') ?? 0);

        if (!in_array($kat, ['NOTARIS', 'PPAT'], true)) {
            return $this->jsonErr('Kategori tidak valid', 422);
        }

        $lapM = new LaporanKerjaModel();
        $lampM = new LaporanLampiranModel();

        try {
            if ($kat === 'NOTARIS') {
                $payload = [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tanggal' => $req->getPost('tanggal'),
                    'sifat' => $req->getPost('sifat'),
                    'nama_penghadap' => $req->getPost('nama_penghadap'),
                    'kuasa' => $req->getPost('kuasa'),
                ];
                $lapId = $lapM->createNotaris($payload, $userId, $payload['tanggal'] ?? null);
            } else {
                // PPAT sesuai form baru
                $payload = [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'akta_no' => $req->getPost('akta_no'),
                    'akta_tgl' => $req->getPost('akta_tgl'),
                    'bentuk' => $req->getPost('bentuk'),
                    'pihak_pengalih' => $req->getPost('pihak_pengalih'),
                    'pihak_penerima' => $req->getPost('pihak_penerima'),
                    'jenis_hak' => $req->getPost('jenis_hak'),
                    'nomor_hak' => $req->getPost('nomor_hak'),
                    'letak' => $req->getPost('letak'),
                    'luas_tnh' => $req->getPost('luas_tnh'),
                    'luas_bgn' => $req->getPost('luas_bgn'),
                    'nilai_transaksi' => $req->getPost('nilai_transaksi'),
                    'sspt_nop' => $req->getPost('sspt_nop'),
                    'sspt_tahun' => $req->getPost('sspt_tahun'),
                    'njop' => $req->getPost('njop'),
                    'sep_nilai' => $req->getPost('sep_nilai'),
                    'sep_tgl' => $req->getPost('sep_tgl'),
                    'bphtb_nilai' => $req->getPost('bphtb_nilai'),
                    'ket' => $req->getPost('ket'),
                ];
                $lapId = $lapM->createPPAT($payload, $userId);
            }

            // handle lampiran (multi)
            $files = $this->request->getFiles();
            $savedNames = [];
            if (isset($files['lampiran'])) {
                $targetDir = WRITEPATH . 'uploads/laporan/';
                if (!is_dir($targetDir))
                    @mkdir($targetDir, 0775, true);

                foreach ($files['lampiran'] as $file) {
                    if (!$file->isValid())
                        continue;
                    $newName = $file->getRandomName();
                    $file->move($targetDir, $newName);
                    $savedNames[] = $newName;
                }
            }
            if (!empty($savedNames)) {
                $lampM->addMany($lapId, $savedNames);
            }

            return $this->jsonOk(['id' => $lapId]);
        } catch (\Throwable $e) {
            return $this->jsonErr('Gagal menyimpan: ' . $e->getMessage(), 500);
        }
    }

    /** DELETE /admin/laporan/delete/{id} */
    public function laporanDelete($id = null)
    {
        if ($x = $this->requireAdminJson())
            return $x;

        $id = (int) $id;
        if ($id <= 0)
            return $this->jsonErr('ID invalid', 422);

        $lapM = new LaporanKerjaModel();
        try {
            $ok = $lapM->deleteWithCascade($id);
            return $this->jsonOk(['deleted' => (bool) $ok]);
        } catch (\Throwable $e) {
            return $this->jsonErr('Gagal menghapus: ' . $e->getMessage(), 500);
        }
    }

    /** GET /admin/laporan/export?bulan=9&tahun=2025&kat=PPAT|NOTARIS
     *  Export PDF sederhana (gunakan Dompdf).
     */
    public function laporanExport()
    {
        if (!$this->isAdmin())
            return redirect()->back()->with('error', 'FORBIDDEN');

        $bulan = (int) ($this->request->getGet('bulan') ?? date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));
        $kat = strtoupper((string) ($this->request->getGet('kat') ?? '')); // opsional, kalau kosong export dua-duanya

        $lapM = new LaporanKerjaModel();
        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'notaris' => $lapM->getMonthly('NOTARIS', $bulan, $tahun),
            'ppat' => $lapM->getMonthly('PPAT', $bulan, $tahun),
        ];

        // siapkan payload terparse
        foreach (['notaris', 'ppat'] as $key) {
            $i = 1;
            foreach ($data[$key] as &$r) {
                $r['row_no'] = $i++;
                $r['payload'] = json_decode((string) ($r['data_json'] ?? '{}'), true) ?: [];
            }
        }

        // pilih view PDF
        $htmlView = null;
        if ($kat === 'NOTARIS') {
            $htmlView = view('pdf/laporan_notaris', $data);
            $filename = "laporan_notaris_{$tahun}-" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . ".pdf";
        } elseif ($kat === 'PPAT') {
            $htmlView = view('pdf/laporan_ppat', $data);
            $filename = "laporan_ppat_{$tahun}-" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . ".pdf";
        } else {
            // kalau gak pilih, export PPAT saja biar cepat; sesuaikan kalau mau gabungan
            $htmlView = view('pdf/laporan_ppat', $data);
            $filename = "laporan_ppat_{$tahun}-" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . ".pdf";
        }

        // Dompdf
        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($htmlView);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function lampiranDelete($lampiranId = null)
    {
        if ($x = $this->requireAdminJson())
            return $x;

        $id = (int) $lampiranId;
        if ($id <= 0)
            return $this->jsonErr('ID invalid', 422);

        $lampM = new LaporanLampiranModel();
        $row = $lampM->find($id);
        if (!$row)
            return $this->jsonErr('Not found', 404);

        // hapus file fisik (jika ada)
        $path = WRITEPATH . 'uploads/laporan/' . $row['file_name'];
        if (is_file($path))
            @unlink($path);

        // hapus di DB
        $lampM->delete($id);

        return $this->jsonOk(['deleted' => true]);
    }
}
