<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\EmployeeResolver;
use App\Libraries\KerjaMenu;
use App\Models\BookingModel;
use App\Models\JadwalModel;
use App\Models\EmployeeModel;
use CodeIgniter\I18n\Time;

class Dashboard extends BaseController
{
    public function index()
    {
        $data['menu'] = KerjaMenu::get();
        if (!session('id')) {
            return redirect()->to('/login');
        }

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $db = db_connect();

        // Tanggal HARI INI (Asia/Jakarta)
        $tz = 'Asia/Jakarta';
        $todayDate = Time::today($tz)->toDateString();
        $startTs = Time::today($tz)->toDateTimeString();
        $endTs = Time::tomorrow($tz)->toDateTimeString();

        $builder = $db->table('booking b')
            ->select('
                b.id, b.status, b.created_at,
                u.nama AS user_nama, u.email AS user_email,
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
            ->orderBy('b.created_at', 'ASC');

        $bookingsToday = $builder->get()->getResultArray();

        // Ringkasan
        $counts = ['confirmed' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($bookingsToday as $r) {
            $s = $this->normalizeStatus($r['status'] ?? '');
            if (in_array($s, ['confirmed', 'approved'], true)) {
                $counts['confirmed']++;
            } elseif ($s === 'pending' || $s === 'booked') {
                $counts['pending']++;
            } elseif ($s === 'completed') {
                $counts['completed']++;
            } elseif ($s === 'canceled') {
                $counts['cancelled']++;
            }
        }

        // Total booking sepanjang waktu (milik karyawan ini)
        $totalBookings = (int) $db->table('booking b')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->groupStart()
            ->where('kj.karyawan_id', $employeeId)
            ->orWhere('b.karyawan_id', $employeeId)
            ->groupEnd()
            ->countAllResults();

        // Data profil header
        $userId = (int) session('id');
        $role = (string) (session('role') ?? 'admin');

        $u = $db->table('users')->select('created_at')->where('id', $userId)->get()->getRowArray();
        $joinYear = '-';
        if (!empty($u['created_at']) && $u['created_at'] !== '0000-00-00 00:00:00') {
            $ts = strtotime((string) $u['created_at']);
            if ($ts !== false) {
                $joinYear = date('Y', $ts);
            }
        }

        $emp = (new EmployeeModel())->asArray()->find($employeeId);

        return view('admin/dashboard', [
            'bookingsToday' => $bookingsToday,
            'counts' => $counts,
            'totalBookings' => $totalBookings,
            'joinYear' => $joinYear,
            'roleLabel' => ucfirst($role),
            'employee' => $emp,
            'userName' => (string) (session('nama') ?? ''),
            'userMail' => (string) (session('email') ?? ''),
        ]);
    }

    public function approve($id)
    {
        $id = (int) $id;
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $m = new BookingModel();
        $row = $m->select('id,karyawan_id,status')->find($id);
        if (!$row)
            return redirect()->to($this->roleDashboard())->with('error', 'Booking tidak ditemukan.');
        if ((int) $row['karyawan_id'] !== (int) $employeeId)
            return redirect()->to($this->roleDashboard())->with('error', 'Tidak berhak menyetujui booking ini.');

        $m->update($row['id'], ['status' => 'confirmed']);

        $back = (string) $this->request->getPost('back');
        $back = $this->sanitizeLocalPath($back) ?: $this->roleDashboard();
        return redirect()->to($back)->with('success', 'Booking disetujui.');
    }

    public function reject($id)
    {
        $id = (int) $id;
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $m = new BookingModel();
        $jm = new JadwalModel();

        $row = $m->select('id,karyawan_id,jadwal_id,status')->find($id);
        if (!$row)
            return redirect()->to($this->roleDashboard())->with('error', 'Booking tidak ditemukan.');
        if ((int) $row['karyawan_id'] !== (int) $employeeId)
            return redirect()->to($this->roleDashboard())->with('error', 'Tidak berhak menolak booking ini.');

        $db = db_connect();
        $db->transStart();
        $m->update($row['id'], ['status' => 'canceled']);
        if (!empty($row['jadwal_id'])) {
            $jm->update($row['jadwal_id'], ['status' => 'available']);
        }
        $db->transComplete();

        $back = (string) $this->request->getPost('back');
        $back = $this->sanitizeLocalPath($back) ?: $this->roleDashboard();
        return redirect()->to($back)->with('success', 'Booking ditolak & slot dikembalikan.');
    }

    public function slot()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $jm = new JadwalModel();

        // Ambil seluruh slot milik karyawan ini
        $list = $jm->where('karyawan_id', $employeeId)
            ->orderBy('tanggal', 'ASC')
            ->orderBy('jam', 'ASC')
            ->findAll(1000);

        // Ambil booking TERBARU per jadwal_id
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
                    $latest[$jid] = $r; // id terbesar = paling akhir
                }
            }
        }

        // Bangun 3 bucket
        $slotsActive = []; // pending/confirmed/approved
        $slotsAvailable = []; // available / canceled
        $slotsCompleted = []; // completed/done

        foreach ($list as $row) {
            $jid = (int) $row['id'];
            $last = $latest[$jid] ?? null;
            $st = $last ? $this->normalizeStatus($last['status'] ?? '') : 'available';
            $lastT = $last['updated_at'] ?? ($last['created_at'] ?? null);

            // record dasar yang dipakai view
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
            } else { // available atau canceled
                $rec['derived_status'] = 'available';
                if ($last && in_array($st, ['canceled'], true)) {
                    $rec['note'] = 'Pernah dibooking & dibatalkan; sekarang Available.';
                    $rec['last_cancel_at'] = $lastT;
                }
                $slotsAvailable[] = $rec;
            }
        }

        // Kirim semua bucket ke view
        return view('admin/slot', [
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
            return redirect()->to(site_url('admin/slot'))
                ->with('warning', 'Slot pada rentang waktu tersebut sudah ada.');
        }

        $jm->insert([
            'karyawan_id' => $employeeId,
            'tanggal' => $tanggal,
            'jam' => $jamStr,
            'status' => 'available',
        ]);

        return redirect()->to(site_url('admin/slot'))
            ->with('success', 'Berhasil menambahkan 1 slot: ' . $jamStr . '.');
    }

    public function slotDelete($id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $id = (int) $id;
        $employeeId = EmployeeResolver::ensureForCurrentUser();

        $jm = new JadwalModel();
        $mBooking = new BookingModel();

        $slot = $jm->where('id', $id)
            ->where('karyawan_id', $employeeId)
            ->first();

        if (!$slot) {
            return redirect()->back()->with('error', 'Slot tidak ditemukan atau bukan milik Anda.');
        }

        // Hanya boleh hapus jika TIDAK ada booking aktif
        $hasActive = $mBooking->where('jadwal_id', $id)
            ->whereIn('status', ['pending', 'confirmed', 'approved'])
            ->first();

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
        $slot = $jm->where('id', $jadwalId)
            ->where('karyawan_id', $employeeId)
            ->first();

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

    private function normalizeStatus(string $status): string
    {
        $s = strtolower(trim($status));
        return match ($s) {
            'approve' => 'approved',
            'reject' => 'canceled',
            'cancel', 'cancelled' => 'canceled',
            'booked' => 'pending',
            'done' => 'completed',
            default => $s,
        };
    }

    private function roleDashboard(): string
    {
        $role = strtolower((string) (session('role') ?? 'user'));
        return match (true) {
            in_array($role, ['admin', 'karyawan', 'pegawai', 'employee', 'staff'], true) => site_url('admin/dashboard'),
            in_array($role, ['multiuser', 'multi-user'], true) => site_url('multiuser/dashboard'),
            default => site_url('user/dashboard'),
        };
    }

    private function sanitizeLocalPath(string $path): string
    {
        $path = trim($path);
        if ($path === '')
            return '';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            return '';
        return site_url('/' . ltrim($path, '/'));
    }
}
