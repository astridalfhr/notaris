<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\EmployeeResolver;
use CodeIgniter\I18n\Time;

class Panel extends BaseController
{
    /**
     * JSON untuk "Jadwal Hari Ini".
     * Mengembalikan semua booking HARI INI yang terkait dengan karyawan milik akun ini
     * (match di kj.karyawan_id ATAU b.karyawan_id). Urut jam.
     *
     * GET /admin/panel/summary
     */
    public function summary()
    {
        if (!session('id')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['ok' => false, 'msg' => 'Unauthorized']);
        }

        $db = db_connect();

        // Karyawan milik akun yang login
        $employeeId = (int) EmployeeResolver::ensureForCurrentUser();

        // Rentang "hari ini" (Asia/Jakarta)
        $tz = 'Asia/Jakarta';
        $todayDate = Time::today($tz)->toDateString();           // YYYY-mm-dd
        $startTs = Time::today($tz)->toDateTimeString();       // YYYY-mm-dd 00:00:00
        $endTs = Time::tomorrow($tz)->toDateTimeString();    // besok 00:00:00

        // Ambil booking yang jatuh "hari ini"
        $rows = $db->table('booking b')
            ->select('b.id,b.status,b.created_at,u.nama AS user_nama,u.email AS user_email,kj.tanggal,kj.jam')
            ->join('users u', 'u.id=b.user_id', 'left')
            ->join('konsultasi_jadwal kj', 'kj.id=b.jadwal_id', 'left')
            // booking terkait karyawan milik akun ini
            ->groupStart()
            ->where('kj.karyawan_id', $employeeId)
            ->orWhere('b.karyawan_id', $employeeId)
            ->groupEnd()
            // yang tanggalnya hari ini (cover DATE dan DATETIME)
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

        // (opsional) ubah status ke lower-case saja agar konsisten
        foreach ($rows as &$r) {
            $r['status'] = strtolower((string) ($r['status'] ?? ''));
        }

        return $this->response->setJSON([
            'ok' => true,
            'bookingsToday' => $rows,     // dipakai view layanan (tabel admin di atas)
        ]);
    }
}
