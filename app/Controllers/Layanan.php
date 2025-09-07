<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;

class Layanan extends BaseController
{
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
            if (!$exists && $has('nama') && $uid > 0) {
                $exists = (bool) $empM->where('nama', 'Admin #' . $uid)->first();
            }
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

        // ===== Jadwal Hari Ini: isi untuk admin & karyawan =====
        $bookingsToday = [];
        $counts = ['confirmed' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];

        // Resolve employee id untuk user saat ini, entah admin atau karyawan
        $employeeId = null;
        try {
            $employeeId = \App\Libraries\EmployeeResolver::ensureForCurrentUser();
        } catch (\Throwable $e) {
            $employeeId = null;
        }

        if ($employeeId) {
            $todayDate = Time::today($tz)->toDateString();
            $startTs = Time::today($tz)->toDateTimeString();
            $endTs = Time::tomorrow($tz)->toDateTimeString();

            $bookingsToday = $db->table('booking b')
                ->select('
                    b.id AS booking_id,
                    b.id, b.status, b.created_at,
                    u.nama AS user_nama, u.email AS user_email,
                    kj.id AS jadwal_id,
                    kj.tanggal, kj.jam
                ')
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
                ->orderBy('kj.jam', 'ASC')
                ->orderBy('b.created_at', 'ASC')
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
        // ======================================

        return view('layanan', [
            'employees' => $employees,
            'date' => $date,
            'serviceSlug' => $serviceSlug,
            'activeService' => $activeService,
            'catalog' => $catalog,
            'isAdmin' => $isAdmin,

            // penting: kirim data ke view
            'bookingsToday' => $bookingsToday,
            'counts' => $counts,
        ]);
    }
}
