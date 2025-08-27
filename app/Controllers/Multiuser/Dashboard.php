<?php
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Libraries\EmployeeResolver;
use App\Models\JadwalModel;
use App\Models\BookingModel;
use App\Models\UserModel;
use App\Models\EmployeeModel;

class Dashboard extends BaseController
{
    public function index()
    {
        date_default_timezone_set('Asia/Jakarta');

        $today = date('Y-m-d');
        $firstDay = date('Y-m-01');
        $lastDay = date('Y-m-t');

        $userId = (int) (session('id') ?? session('user_id') ?? 0);

        $rawUser = (new UserModel())->find($userId) ?? [];
        $myEmployeeId = (int) EmployeeResolver::ensureForCurrentUser();
        $emp = (new EmployeeModel())->find($myEmployeeId) ?? [];

        $displayName = trim((string) ($emp['nama'] ?? $rawUser['nama'] ?? $rawUser['name'] ?? '-'));

        $photo = '';
        if (!empty($emp['foto']) && is_file(FCPATH . 'images/karyawan/' . $emp['foto'])) {
            $photo = base_url('images/karyawan/' . $emp['foto']);
        } elseif (!empty($rawUser['profile_photo']) && filter_var($rawUser['profile_photo'], FILTER_VALIDATE_URL)) {
            $photo = $rawUser['profile_photo'];
        } elseif (!empty(session('profile_photo'))) {
            $photo = (string) session('profile_photo');
        }

        $bm = new BookingModel();
        $rows = $bm->select([
            'booking.id',
            'booking.user_id',
            'booking.karyawan_id',
            'booking.jadwal_id',
            'booking.status',
            'users.nama AS user_nama',
            'employees.nama AS karyawan_nama',
            'kj.tanggal AS tanggal_jadwal',
            'kj.jam AS jadwal_jam'
        ])
            ->join('users', 'users.id = booking.user_id', 'left')
            ->join('employees', 'employees.id = booking.karyawan_id', 'left')
            ->join('konsultasi_jadwal kj', 'kj.id = booking.jadwal_id', 'left')
            ->where('DATE(kj.tanggal)', $today)
            ->orderBy('employees.nama', 'ASC')
            ->orderBy('kj.jam', 'ASC')
            ->findAll();

        $groups = [];
        $myTotalToday = 0;

        foreach ($rows as $r) {
            $gid = (int) ($r['karyawan_id'] ?? 0);
            if (!isset($groups[$gid])) {
                $groups[$gid] = [
                    'karyawan_id' => $gid,
                    'karyawan_nama' => $r['karyawan_nama'] ?? 'Tanpa Nama',
                    'items' => [],
                ];
            }
            $groups[$gid]['items'][] = [
                'id' => $r['id'] ?? null,
                'user_nama' => $r['user_nama'] ?? '-',
                'jam' => $r['jadwal_jam'] ?: '-',
                'status' => $r['status'] ?? 'pending',
                'tanggal' => $r['tanggal_jadwal'] ?? null,
            ];

            if ($gid === $myEmployeeId) {
                $myTotalToday++;
            }
        }

        $db = db_connect();

        // ====== TOTAL BOOKING UNTUK HEADER PROFIL (REAL-TIME, MILIK KARYAWAN INI) ======
        // Hitung semua booking sepanjang waktu untuk karyawan yang login,
        // abaikan yang dibatalkan.
        $totalAll = (int) $db->table('booking')
            ->where('karyawan_id', $myEmployeeId)
            ->whereNotIn('status', ['canceled', 'cancelled']) // jaga-jaga dua ejaan
            ->countAllResults();
        // ================================================================================

        // Statistik Booking (TIDAK diubah)
        $todayAll = $db->table('booking b')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->where('DATE(kj.tanggal)', $today)
            ->countAllResults();

        $todayMine = $db->table('booking b')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->where('DATE(kj.tanggal)', $today)
            ->where('b.karyawan_id', $myEmployeeId)
            ->countAllResults();

        $monthAll = $db->table('booking b')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->where('kj.tanggal >=', $firstDay)
            ->where('kj.tanggal <=', $lastDay)
            ->countAllResults();

        $monthMine = $db->table('booking b')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->where('kj.tanggal >=', $firstDay)
            ->where('kj.tanggal <=', $lastDay)
            ->where('b.karyawan_id', $myEmployeeId)
            ->countAllResults();

        $stat = [
            'today_all' => (int) $todayAll,
            'today_me' => (int) $todayMine,
            'month_all' => (int) $monthAll,
            'month_me' => (int) $monthMine,
        ];

        $joinYear = '-';
        if (!empty($rawUser['created_at']) && $rawUser['created_at'] !== '0000-00-00 00:00:00') {
            $ts = strtotime((string) $rawUser['created_at']);
            if ($ts)
                $joinYear = date('Y', $ts);
        }

        $user = [
            'nama' => $displayName,
            'email' => $rawUser['email'] ?? '-',
            'role' => $rawUser['role'] ?? 'multiuser',
            'profile_photo' => $photo,
            'created_at' => $rawUser['created_at'] ?? null,
        ];

        return view('multiuser/dashboard', [
            'today' => $today,
            'user' => $user,
            'groups' => $groups,
            'myEmployeeId' => $myEmployeeId,
            'myTotalToday' => $myTotalToday,
            'joinYear' => $joinYear,
            'stat' => $stat,
            'totalAll' => $totalAll, // dipakai di header profil
        ]);
    }

    public function bookingConfirm(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $myEmployeeId = (int) EmployeeResolver::ensureForCurrentUser();
        $db = db_connect();

        $row = $db->table('booking')
            ->where('id', $id)
            ->where('karyawan_id', $myEmployeeId)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->back()->with('error', 'Tidak berhak mengkonfirmasi booking ini.');
        }
        if (strtolower((string) $row['status']) !== 'pending') {
            return redirect()->back()->with('warning', 'Booking ini sudah diproses.');
        }

        $db->table('booking')->where('id', $id)->update([
            'status' => 'confirmed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $back = (string) ($this->request->getPost('back') ?? '');
        if ($back !== '') {
            if (preg_match('#^https?://#i', $back)) {
                return redirect()->to($back)->with('success', 'Booking dikonfirmasi.');
            }
            $back = ltrim($back, '/');
            $back = preg_replace('#^index\.php/+?#', '', $back);
            return redirect()->to(site_url($back))->with('success', 'Booking dikonfirmasi.');
        }
        return redirect()->to(site_url('multiuser'))->with('success', 'Booking dikonfirmasi.');
    }

    public function users()
    {
        return redirect()->to(site_url('multiuser/site'));
    }

    public function employees()
    {
        $rows = (new EmployeeModel())->orderBy('nama', 'asc')->findAll();
        return view('multiuser/employees_index', ['rows' => $rows]);
    }

    public function setRole(int $id)
    {
        return redirect()->back()->with('warning', 'Akses tidak diizinkan.');
    }

    public function slot()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $jm = new JadwalModel();

        $list = $jm->where('karyawan_id', $employeeId)
            ->orderBy('tanggal', 'ASC')
            ->orderBy('jam', 'ASC')
            ->findAll(1000);

        $ids = array_column($list, 'id');
        $latest = [];
        if ($ids) {
            $rows = db_connect()->table('booking')
                ->select('id, jadwal_id, status, created_at, updated_at')
                ->whereIn('jadwal_id', $ids)
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $jid = (int) $r['jadwal_id'];
                if (!isset($latest[$jid])) {
                    $latest[$jid] = $r;
                }
            }
        }

        $slotsActive = [];
        $slotsAvailable = [];
        $slotsCompleted = [];

        foreach ($list as $row) {
            $jid = (int) $row['id'];
            $last = $latest[$jid] ?? null;
            $st = $last ? $this->normalizeStatus($last['status'] ?? '') : 'available';

            $rec = [
                'jadwal_id' => $jid,
                'tanggal' => $row['tanggal'] ?? null,
                'jam' => $row['jam'] ?? '-',
                'note' => null,
                'last_cancel_at' => null,
            ];

            if (in_array($st, ['pending', 'confirmed', 'approved'], true)) {
                $rec['derived_status'] = 'booked';
                $slotsActive[] = $rec;
            } elseif ($st === 'completed') {
                $rec['derived_status'] = 'completed';
                $slotsCompleted[] = $rec;
            } else {
                $rec['derived_status'] = 'available';
                if ($last && in_array($st, ['canceled'], true)) {
                    $rec['note'] = 'Pernah dibooking & dibatalkan; sekarang Available.';
                    $rec['last_cancel_at'] = $last['updated_at'] ?? ($last['created_at'] ?? null);
                }
                $slotsAvailable[] = $rec;
            }
        }

        return view('multiuser/slot', [
            'slotsActive' => $slotsActive,
            'slotsAvailable' => $slotsAvailable,
            'slotsCompleted' => $slotsCompleted,
        ]);
    }

    public function slotStore()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $mulaiStr = trim((string) $this->request->getPost('mulai'));
        $akhirStr = trim((string) $this->request->getPost('sampai'));

        if ($tanggal === '' || $mulaiStr === '' || $akhirStr === '') {
            return redirect()->back()->withInput()->with('error', 'Lengkapi tanggal, dari jam, dan sampai jam.');
        }

        try {
            $mulai = new \DateTime($tanggal . ' ' . $mulaiStr);
            $akhir = new \DateTime($tanggal . ' ' . $akhirStr);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Format jam tidak valid.');
        }

        if ($akhir <= $mulai) {
            return redirect()->back()->withInput()->with('error', '"Sampai jam" harus lebih besar dari "Dari jam".');
        }

        $jamStr = $mulai->format('H:i') . '–' . $akhir->format('H:i');

        $jm = new JadwalModel();
        $exists = $jm->where('karyawan_id', $employeeId)
            ->where('tanggal', $tanggal)
            ->where('jam', $jamStr)
            ->first();

        if ($exists) {
            return redirect()->to(site_url('multiuser/slot'))->with('warning', 'Slot pada rentang waktu tersebut sudah ada.');
        }

        $jm->insert([
            'karyawan_id' => $employeeId,
            'tanggal' => $tanggal,
            'jam' => $jamStr,
            'status' => 'available',
        ]);

        return redirect()->to(site_url('multiuser/slot'))->with('success', 'Berhasil menambahkan 1 slot: ' . $jamStr . '.');
    }

    public function slotDelete($id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $id = (int) $id;
        $employeeId = EmployeeResolver::ensureForCurrentUser();

        $jm = new JadwalModel();
        $mBooking = new BookingModel();

        $slot = $jm->where('id', $id)->where('karyawan_id', $employeeId)->first();
        if (!$slot) {
            return redirect()->back()->with('error', 'Slot tidak ditemukan atau bukan milik Anda.');
        }

        $hasActive = $mBooking->where('jadwal_id', $id)->whereIn('status', ['pending', 'confirmed', 'approved'])->first();
        if ($hasActive) {
            return redirect()->back()->with('error', 'Slot sudah/masih dibooking, tidak bisa dihapus.');
        }

        $jm->delete($id);
        return redirect()->back()->with('success', 'Slot berhasil dihapus.');
    }

    public function slotComplete($jadwalId = null)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $jadwalId = (int) $jadwalId;
        if ($jadwalId <= 0) {
            return redirect()->back()->with('error', 'ID jadwal tidak valid.');
        }

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $jm = new JadwalModel();
        $slot = $jm->where('id', $jadwalId)->where('karyawan_id', $employeeId)->first();

        if (!$slot) {
            return redirect()->back()->with('error', 'Slot tidak ditemukan atau bukan milik Anda.');
        }

        $db = db_connect();
        $active = $db->table('booking')
            ->where('jadwal_id', $jadwalId)
            ->whereIn('status', ['pending', 'confirmed', 'approved'])
            ->orderBy('id', 'DESC')
            ->get()->getRowArray();

        if (!$active) {
            return redirect()->back()->with('warning', 'Tidak ada booking aktif pada slot ini.');
        }

        $ok = $db->table('booking')->where('id', (int) $active['id'])->update([
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            return redirect()->back()->with('error', 'Gagal menandai sebagai selesai.');
        }

        return redirect()->back()->with('success', 'Booking pada slot ini ditandai sebagai selesai.');
    }

    public function slotDetail(int $jadwalId)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $myEmployeeId = (int) EmployeeResolver::ensureForCurrentUser();

        $jm = new JadwalModel();
        $slot = $jm->where('id', $jadwalId)
            ->where('karyawan_id', $myEmployeeId)
            ->first();

        if (!$slot) {
            return redirect()->back()->with('error', 'Slot tidak ditemukan atau bukan milik Anda.');
        }

        $db = db_connect();
        $b = $db->table('booking b')
            ->select('b.*, u.nama AS user_nama, u.email AS user_email, e.nama AS karyawan_nama, e.foto AS karyawan_foto')
            ->join('users u', 'u.id = b.user_id', 'left')
            ->join('employees e', 'e.id = b.karyawan_id', 'left')
            ->where('b.jadwal_id', $jadwalId)
            ->orderBy('b.id', 'DESC')
            ->get()->getRowArray();

        $status = $b ? strtolower((string) $b['status']) : strtolower((string) ($slot['status'] ?? 'available'));
        if (method_exists($this, 'normalizeStatus')) {
            $status = $this->normalizeStatus($status);
        }

        $jam = (string) ($slot['jam'] ?? '');
        $jamMulai = $jamSelesai = null;
        if (strpos($jam, '–') !== false) {
            [$jamMulai, $jamSelesai] = array_map('trim', explode('–', $jam, 2));
        }

        $detail = [
            'id' => $b['id'] ?? null,
            'status' => $status,
            'tanggal' => $slot['tanggal'] ?? null,
            'jam' => $jam ?: null,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'created_at' => $b['created_at'] ?? null,
            'catatan' => $b['catatan'] ?? ($b['keluhan'] ?? null),

            'user_nama' => $b['user_nama'] ?? null,
            'user_email' => $b['user_email'] ?? null,

            'karyawan_nama' => $b['karyawan_nama'] ?? null,
            'karyawan_foto' => (!empty($b['karyawan_foto']) && is_file(FCPATH . 'images/karyawan/' . $b['karyawan_foto']))
                ? $b['karyawan_foto'] : null,
            'karyawan_jabatan' => 'Pegawai',
            'karyawan_spesialisasi' => 'Layanan Konsultasi',
        ];

        return view('multiuser/slot_detail', ['detail' => $detail]);
    }

    private function normalizeStatus(string $s): string
    {
        $s = strtolower(trim($s));
        return $s ?: 'pending';
    }
}
