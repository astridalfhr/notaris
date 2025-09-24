<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LaporanKerjaModel;
use App\Models\LaporanLampiranModel;
use CodeIgniter\I18n\Time;

class Laporan extends BaseController
{
    /* ======================== Guards & helpers ======================== */

    private function isAdmin(): bool
    {
        $role = strtolower((string) (session('role') ?? session('user.role') ?? ''));
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
    public function feed()
    {
        if ($x = $this->requireAdminJson())
            return $x;

        $bulan = (int) ($this->request->getGet('bulan') ?? date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));

        $lapM = new \App\Models\LaporanKerjaModel();
        $lampM = new \App\Models\LaporanLampiranModel();

        $rowsNot = $lapM->getMonthly('NOTARIS', $bulan, $tahun);
        $rowsPpt = $lapM->getMonthly('PPAT', $bulan, $tahun);

        // Ambil semua ID lalu tarik lampiran per laporan_id
        $allIds = array_map(fn($r) => (int) $r['id'], array_merge($rowsNot, $rowsPpt));
        $filesByLap = [];
        if ($allIds) {
            $files = $lampM->select('id, laporan_id, file_name, original, mime, size')
                ->whereIn('laporan_id', $allIds)
                ->orderBy('id', 'ASC')
                ->findAll();
            foreach ($files as $f) {
                $filesByLap[(int) $f['laporan_id']][] = [
                    'id' => (int) $f['id'],
                    'original' => (string) ($f['original'] ?: $f['file_name']),
                    'file_name' => (string) $f['file_name'],
                    'mime' => (string) ($f['mime'] ?? ''),
                    'size' => (int) ($f['size'] ?? 0),
                ];
            }
        }

        $map = function (array $rows) use ($filesByLap): array {
            $i = 1;
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'id' => (int) $r['id'],
                    'row_no' => $i++,
                    'nomor_bulanan' => (int) ($r['nomor_bulanan'] ?? 0),
                    'tanggal' => (string) ($r['tanggal'] ?? ''),
                    'payload' => json_decode((string) ($r['data_json'] ?? '{}'), true) ?: [],
                    'created_at' => (string) ($r['created_at'] ?? ''),
                    'created_by' => (int) ($r['created_by'] ?? 0),
                    'files' => $filesByLap[(int) $r['id']] ?? [],
                ];
            }
            return $out;
        };

        return $this->jsonOk([
            'bulan' => $bulan,
            'tahun' => $tahun,
            'notaris' => $map($rowsNot),
            'ppat' => $map($rowsPpt),
        ]);
    }
    public function list()
    {
        return $this->feed();
    }
    public function upload()
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

        // Validasi minimal per kategori
        $rules = [
            'kat' => 'required|in_list[NOTARIS,PPAT]',
            'bulan' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[12]',
            'tahun' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
        ];
        if ($kat === 'NOTARIS') {
            $rules += [
                'tanggal' => 'required|valid_date[Y-m-d]',
                'sifat' => 'required|string|max_length[50]',
                'nama_penghadap' => 'required|string|max_length[255]',
            ];
        } else {
            $rules += [
                'akta_tgl' => 'permit_empty|valid_date[Y-m-d]',
            ];
        }
        if (!$this->validate($rules)) {
            return $this->jsonErr(implode("\n", $this->validator->getErrors()), 422);
        }

        $lapM = new LaporanKerjaModel();
        $lampM = new LaporanLampiranModel();

        try {
            // 1) Simpan header laporan
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
                    'bphtb_tgl' => $req->getPost('bphtb_tgl'), 
                    'bphtb_nilai' => $req->getPost('bphtb_nilai'),
                    'ket' => $req->getPost('ket'),
                ];
                $lapId = $lapM->createPPAT($payload, $userId);
            }

            // 2) Lampiran multiple (opsional) — ambil metadata sebelum move
            $files = $req->getFileMultiple('lampiran');
            if (!empty($files)) {
                $dir = WRITEPATH . 'uploads/laporan';
                if (!is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                }

                foreach ($files as $f) {
                    if (!$f || !$f->isValid() || $f->hasMoved()) {
                        continue;
                    }

                    // ambil meta SEBELUM move (menghindari finfo temp)
                    $orig = $f->getClientName();
                    $mime = $f->getClientMimeType();
                    $size = $f->getSize();
                    $safe = $f->getRandomName();

                    // move
                    $f->move($dir, $safe);
                    $absPath = $dir . DIRECTORY_SEPARATOR . $safe;

                    // fallback jaga-jaga: jika client mime kosong, cek dari file final
                    if (!$mime && is_file($absPath)) {
                        $mime = @mime_content_type($absPath) ?: 'application/octet-stream';
                    }

                    // simpan record lampiran langsung, tanpa helper yang baca TMP lagi
                    $lampM->insert([
                        'laporan_id' => $lapId,
                        'kategori' => $kat,
                        'file_name' => $safe,
                        'original' => $orig,
                        'mime' => $mime,
                        'size' => $size,
                        'path' => 'writable/uploads/laporan/' . $safe,
                        'created_at' => Time::now('Asia/Jakarta')->toDateTimeString(),
                    ]);
                }
            }

            return $this->jsonOk(['id' => $lapId]);
        } catch (\Throwable $e) {
            log_message('error', 'LAPORAN_UPLOAD_FAIL: {msg} {trace}', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->jsonErr('Gagal menyimpan', 500);
        }
    }

    /* ====== Alias agar kompatibel dengan view (store → upload) ====== */
    public function store()
    {
        return $this->upload();
    }

    /* ======================== DOWNLOAD LAMPIRAN ======================== */
    // GET /admin/laporan/download/{lampiranId}
    public function download($lampiranId = null)
    {
        if (!$this->isAdmin()) {
            return redirect()->back()->with('error', 'FORBIDDEN');
        }
        $lampiranId = (int) $lampiranId;
        if ($lampiranId <= 0) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $lampM = new LaporanLampiranModel();
        $row = $lampM->find($lampiranId);
        if (!$row) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $path = WRITEPATH . 'uploads/laporan/' . $row['file_name'];
        if (!is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('File missing');
        }

        $downloadName = $row['original'] ?: $row['file_name'];
        return $this->response
            ->download($path, null)
            ->setFileName($this->safeFilename($downloadName));
    }

    /* ======================== DELETE ======================== */
    // DELETE /admin/laporan/{id}  atau  POST /admin/laporan/delete/{id}
    public function delete($id = null)
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

    /* ======================== EXPORT PDF ======================== */
    // GET /admin/laporan/export?bulan=9&tahun=2025&kat=PPAT|NOTARIS
    public function export()
    {
        if (!$this->isAdmin())
            return redirect()->back()->with('error', 'FORBIDDEN');

        $bulan = (int) ($this->request->getGet('bulan') ?? date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?? date('Y'));
        $kat = strtoupper((string) ($this->request->getGet('kat') ?? 'PPAT')); // default PPAT

        $lapM = new LaporanKerjaModel();
        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'notaris' => $lapM->getMonthly('NOTARIS', $bulan, $tahun),
            'ppat' => $lapM->getMonthly('PPAT', $bulan, $tahun),
        ];
        foreach (['notaris', 'ppat'] as $key) {
            $i = 1;
            foreach ($data[$key] as &$r) {
                $r['row_no'] = $i++;
                $r['payload'] = json_decode((string) ($r['data_json'] ?? '{}'), true) ?: [];
            }
        }

        // ==== bedakan 3 pilihan: NOTARIS | PPAT | ALL ====
        switch ($kat) {
            case 'NOTARIS':
                $html = view('pdf/laporan_notaris', $data);
                $filename = "laporan_notaris_{$tahun}-" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . ".pdf";
                break;
            case 'PPAT':
                $html = view('pdf/laporan_ppat', $data);
                $filename = "laporan_ppat_{$tahun}-" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . ".pdf";
                break;
            case 'ALL':
            default:
                // gabungan dalam satu PDF (Notaris + PPAT)
                $html = view('pdf/laporan_all', $data);
                $filename = "laporan_bulanan_all_{$tahun}-" . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) . ".pdf";
                break;
        }

        $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    public function open($lampiranId = null)
    {
        if (!$this->isAdmin())
            return redirect()->back()->with('error', 'FORBIDDEN');

        $id = (int) $lampiranId;
        $lampM = new LaporanLampiranModel();
        $row = $lampM->find($id);
        if (!$row)
            return $this->response->setStatusCode(404)->setBody('Not found');

        $path = WRITEPATH . 'uploads/laporan/' . $row['file_name'];
        if (!is_file($path))
            return $this->response->setStatusCode(404)->setBody('File missing');

        $mime = (string) ($row['mime'] ?? '');
        if ($mime === '') {
            $mime = @mime_content_type($path) ?: 'application/octet-stream';
        }
        $name = (string) ($row['original'] ?: $row['file_name']);

        // perbaiki: "filename" (bukan file_name)
        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Disposition', 'inline; filename="' . $this->safeFilename($name) . '"')
            ->setBody(file_get_contents($path));
    }

    // Tambahkan helper kecil di class yang sama:
    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[^\pL\pN\.\-\_\s]+/u', '_', $name) ?? 'file';
        $name = trim($name);
        return $name !== '' ? $name : 'file';
    }

    public function update($id = null)
    {
        if ($x = $this->requireAdminJson())
            return $x;

        $id = (int) $id;
        if ($id <= 0)
            return $this->jsonErr('ID invalid', 422);

        $req = $this->request;
        $kat = strtoupper((string) $req->getPost('kat'));
        if (!in_array($kat, ['NOTARIS', 'PPAT'], true)) {
            return $this->jsonErr('Kategori tidak valid', 422);
        }

        $lapM = new LaporanKerjaModel();
        $lampM = new LaporanLampiranModel();

        // --- rakit payload yang disimpan ke data_json ---
        if ($kat === 'NOTARIS') {
            $payload = [
                'bulan' => (int) ($req->getPost('bulan') ?? date('n')),
                'tahun' => (int) ($req->getPost('tahun') ?? date('Y')),
                'tanggal' => $req->getPost('tanggal'),
                'sifat' => $req->getPost('sifat'),
                'nama_penghadap' => $req->getPost('nama_penghadap'),
                'kuasa' => $req->getPost('kuasa'),
            ];
            $tanggal = $payload['tanggal'] ?? null;
        } else {
            $payload = [
                'bulan' => (int) ($req->getPost('bulan') ?? date('n')),
                'tahun' => (int) ($req->getPost('tahun') ?? date('Y')),
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
                'bphtb_tgl' => $req->getPost('bphtb_tgl'), 
                'bphtb_nilai' => $req->getPost('bphtb_nilai'),
                'ket' => $req->getPost('ket'),
            ];
            $tanggal = $payload['akta_tgl'] ?? null; // untuk kolom tanggal di tabel
        }

        // --- update header laporan (langsung) ---
        $ok = $lapM->where('id', $id)->set([
            'data_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'tanggal' => $tanggal,
        ])->update();

        if ($ok === false) {
            // supaya error 500-nya kelihatan jelas
            return $this->jsonErr('Gagal update: ' . json_encode($lapM->errors()), 500);
        }

        // --- lampiran baru (opsional) ---
        $files = $req->getFileMultiple('lampiran');
        if (!empty($files)) {
            $dir = WRITEPATH . 'uploads/laporan';
            if (!is_dir($dir))
                @mkdir($dir, 0775, true);

            foreach ($files as $f) {
                if (!$f || !$f->isValid() || $f->hasMoved())
                    continue;

                $orig = $f->getClientName();
                $mime = $f->getClientMimeType();
                $size = $f->getSize();
                $safe = $f->getRandomName();
                $f->move($dir, $safe);

                if (!$mime && is_file($dir . '/' . $safe)) {
                    $mime = @mime_content_type($dir . '/' . $safe) ?: 'application/octet-stream';
                }

                $lampM->insert([
                    'laporan_id' => $id,
                    'kategori' => $kat,
                    'file_name' => $safe,
                    'original' => $orig,
                    'mime' => $mime,
                    'size' => $size,
                    'path' => 'writable/uploads/laporan/' . $safe,
                    'created_at' => \CodeIgniter\I18n\Time::now('Asia/Jakarta')->toDateTimeString(),
                ]);
            }
        }

        return $this->jsonOk(['updated' => true]);
    }

    public function deleteLampiran($lampiranId = null)
    {
        if ($x = $this->requireAdminJson())
            return $x;

        $id = (int) $lampiranId;
        if ($id <= 0)
            return $this->jsonErr('ID lampiran invalid', 422);

        $lampM = new \App\Models\LaporanLampiranModel();
        $row = $lampM->find($id);
        if (!$row)
            return $this->jsonErr('Lampiran tidak ditemukan', 404);

        // hapus file fisik (jika ada)
        $path = WRITEPATH . 'uploads/laporan/' . $row['file_name'];
        if (is_file($path)) {
            @unlink($path);
        }

        // hapus record DB
        try {
            $lampM->delete($id);
            return $this->jsonOk(['deleted' => true]);
        } catch (\Throwable $e) {
            return $this->jsonErr('Gagal menghapus lampiran', 500);
        }
    }
}
