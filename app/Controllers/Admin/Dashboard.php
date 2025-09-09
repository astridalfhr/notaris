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
    /** Normalisasi status jadi konsisten */
    private function normalizeStatus(string $status): string
    {
        $s = strtolower(trim($status));
        return match ($s) {
            'approve', 'approved', 'confirm', 'confirmed' => 'confirmed',
            'reject', 'cancel', 'cancelled', 'canceled' => 'cancelled',
            'booked', 'pending' => 'pending',
            'done', 'completed', 'finish' => 'completed',
            default => $s ?: 'pending',
        };
    }

    /** Fallback redirect dashboard sesuai role */
    private function roleDashboard(): string
    {
        $role = strtolower((string) (session('role') ?? 'user'));
        return in_array($role, ['admin', 'karyawan', 'pegawai', 'employee', 'staff'], true)
            ? site_url('layanan')
            : site_url('user/dashboard');
    }

    /** Sanitasi local path */
    private function sanitizeLocalPath(string $path): string
    {
        $path = trim($path);
        if ($path === '')
            return '';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://'))
            return '';
        return site_url('/' . ltrim($path, '/'));
    }

    /** Dashboard “Layanan” */
    public function index()
    {
        $data['menu'] = KerjaMenu::get();
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $db = db_connect();

        $tz = 'Asia/Jakarta';
        $todayDate = Time::today($tz)->toDateString();

        $builder = $db->table('booking b')
            ->select('
                b.id, b.status, b.created_at, b.updated_at, b.jadwal_id AS slot_id,
                u.nama AS user_nama, u.email AS user_email,
                kj.tanggal, kj.jam
            ')
            ->join('users u', 'u.id = b.user_id', 'left')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->groupStart()
            ->where('kj.karyawan_id', $employeeId)
            ->orWhere('b.karyawan_id', $employeeId)
            ->groupEnd()
            ->where('DATE(kj.tanggal)', $todayDate)
            ->orderBy('kj.jam', 'ASC')
            ->orderBy('b.id', 'DESC');

        $bookingsToday = $builder->get()->getResultArray();

        $counts = ['confirmed' => 0, 'pending' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($bookingsToday as $r) {
            $s = $this->normalizeStatus($r['status'] ?? '');
            $counts[$s] = ($counts[$s] ?? 0) + 1;
        }

        $totalBookings = (int) $db->table('booking b')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->groupStart()
            ->where('kj.karyawan_id', $employeeId)
            ->orWhere('b.karyawan_id', $employeeId)
            ->groupEnd()
            ->countAllResults();

        $userId = (int) session('id');
        $role = (string) (session('role') ?? 'admin');

        $u = $db->table('users')->select('created_at')->where('id', $userId)->get()->getRowArray();
        $joinYear = '-';
        if (!empty($u['created_at']) && $u['created_at'] !== '0000-00-00 00:00:00') {
            $ts = strtotime((string) $u['created_at']);
            if ($ts !== false)
                $joinYear = date('Y', $ts);
        }

        $emp = (new EmployeeModel())->asArray()->find($employeeId);

        return view('layanan', [
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

    /** Approve booking */
    public function approve($id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $m = new BookingModel();
        $jm = new JadwalModel();

        $row = $m->find($id);
        if (!$row)
            return redirect()->to($this->roleDashboard())->with('error', 'Booking tidak ditemukan.');
        if ((int) $row['karyawan_id'] !== (int) $employeeId)
            return redirect()->to($this->roleDashboard())->with('error', 'Tidak berhak.');

        $m->update($id, ['status' => 'confirmed', 'updated_at' => date('Y-m-d H:i:s')]);

        if (!empty($row['jadwal_id'])) {
            $jm->update($row['jadwal_id'], ['status' => 'booked']);
        }

        $back = $this->sanitizeLocalPath((string) $this->request->getPost('back')) ?: $this->roleDashboard();
        return redirect()->to($back)->with('success', 'Booking disetujui.');
    }

    /** Reject booking */
    public function reject($id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $m = new BookingModel();
        $jm = new JadwalModel();

        $row = $m->find($id);
        if (!$row)
            return redirect()->to($this->roleDashboard())->with('error', 'Booking tidak ditemukan.');
        if ((int) $row['karyawan_id'] !== (int) $employeeId)
            return redirect()->to($this->roleDashboard())->with('error', 'Tidak berhak.');

        $m->update($id, ['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);

        if (!empty($row['jadwal_id'])) {
            $jm->update($row['jadwal_id'], ['status' => 'available']);
        }

        $back = $this->sanitizeLocalPath((string) $this->request->getPost('back')) ?: $this->roleDashboard();
        return redirect()->to($back)->with('success', 'Booking ditolak & slot dikembalikan.');
    }

    /** List slot */
    public function slot()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $jm = new JadwalModel();

        $list = $jm->where('karyawan_id', $employeeId)
            ->orderBy('tanggal', 'ASC')->orderBy('jam', 'ASC')
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
                if (!isset($latest[$jid]))
                    $latest[$jid] = $r;
            }
        }

        $slotsActive = [];
        $slotsAvailable = [];
        $slotsCompleted = [];

        foreach ($list as $row) {
            $jid = (int) $row['id'];
            $last = $latest[$jid] ?? null;
            $st = $last ? $this->normalizeStatus($last['status'] ?? '') : 'available';
            $lastT = $last['updated_at'] ?? ($last['created_at'] ?? null);

            $rec = [
                'jadwal_id' => $jid,
                'tanggal' => $row['tanggal'] ?? null,
                'jam' => $row['jam'] ?? '-',
                'note' => null,
                'last_cancel_at' => null,
            ];

            if (in_array($st, ['pending', 'confirmed'], true)) {
                $rec['derived_status'] = 'booked';
                $slotsActive[] = $rec;
            } elseif ($st === 'completed') {
                $rec['derived_status'] = 'completed';
                $slotsCompleted[] = $rec;
            } else {
                $rec['derived_status'] = 'available';
                if ($last && $st === 'cancelled') {
                    $rec['note'] = 'Pernah dibooking & dibatalkan; sekarang Available.';
                    $rec['last_cancel_at'] = $lastT;
                }
                $slotsAvailable[] = $rec;
            }
        }

        return view('admin/slot', [
            'slotsActive' => $slotsActive,
            'slotsAvailable' => $slotsAvailable,
            'slotsCompleted' => $slotsCompleted,
        ]);
    }

    /** Tambah slot */
    public function slotStore()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $mulaiStr = trim((string) $this->request->getPost('mulai'));
        $akhirStr = trim((string) $this->request->getPost('sampai'));

        if ($tanggal === '' || $mulaiStr === '' || $akhirStr === '')
            return redirect()->back()->withInput()->with('error', 'Lengkapi tanggal & jam.');

        try {
            $mulai = new \DateTime($tanggal . ' ' . $mulaiStr);
            $akhir = new \DateTime($tanggal . ' ' . $akhirStr);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Format jam tidak valid.');
        }

        if ($akhir <= $mulai)
            return redirect()->back()->withInput()->with('error', '"Sampai jam" harus > "Dari jam".');

        $jamStr = $mulai->format('H:i') . '–' . $akhir->format('H:i');

        $jm = new JadwalModel();
        $exists = $jm->where('karyawan_id', $employeeId)
            ->where('tanggal', $tanggal)->where('jam', $jamStr)->first();

        if ($exists)
            return redirect()->to(site_url('admin/slot'))->with('warning', 'Slot sudah ada.');

        $jm->insert([
            'karyawan_id' => $employeeId,
            'tanggal' => $tanggal,
            'jam' => $jamStr,
            'status' => 'available',
        ]);

        return redirect()->to(site_url('admin/slot'))->with('success', 'Slot ditambahkan: ' . $jamStr);
    }

    /** Hapus slot */
    public function slotDelete($id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $id = (int) $id;
        $employeeId = EmployeeResolver::ensureForCurrentUser();

        $jm = new JadwalModel();
        $mBooking = new BookingModel();

        $slot = $jm->where('id', $id)->where('karyawan_id', $employeeId)->first();
        if (!$slot)
            return redirect()->back()->with('error', 'Slot tidak ditemukan.');

        $hasActive = $mBooking->where('jadwal_id', $id)
            ->whereIn('status', ['pending', 'confirmed'])->first();

        if ($hasActive)
            return redirect()->back()->with('error', 'Slot masih dibooking, tidak bisa dihapus.');

        $jm->delete($id);
        return redirect()->back()->with('success', 'Slot dihapus.');
    }

    /** Tandai slot selesai */
    /** Tandai slot selesai */
    public function slotComplete($jadwalId = null)
    {
        if (!session('id')) {
            return redirect()->to('/login');
        }

        $jadwalId = (int) $jadwalId;
        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $jm = new JadwalModel();

        // Pastikan slot milik karyawan ini
        $slot = $jm->where('id', $jadwalId)
            ->where('karyawan_id', $employeeId)
            ->first();
        if (!$slot) {
            return redirect()->back()->with('error', 'Slot tidak ditemukan.');
        }

        $db = db_connect();

        // Ambil booking TERAKHIR untuk slot ini, apapun statusnya
        $last = $db->table('booking')
            ->where('jadwal_id', $jadwalId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($last) {
            $raw = strtolower((string) ($last['status'] ?? ''));

            // Status yang dianggap "aktif": belum selesai / belum batal
            $isActiveLike = in_array($raw, [
                'pending',
                'confirmed',
                'booked',
                'approve',
                'approved',
                'confirm'
            ], true);

            if ($isActiveLike) {
                $db->table('booking')
                    ->where('id', (int) $last['id'])
                    ->update([
                        'status' => 'completed',
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }
            // Jika sudah completed/cancelled, biarkan saja (idempotent)
        }

        // Slot dibuat available lagi supaya bisa dipakai ulang
        $jm->update($jadwalId, ['status' => 'available']);

        return redirect()->back()->with('success', 'Slot ditandai selesai.');
    }

    /** Detail slot */
    public function slotDetail(int $jadwalId)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $myEmployeeId = (int) EmployeeResolver::ensureForCurrentUser();

        $jm = new JadwalModel();
        $slot = $jm->where('id', $jadwalId)->where('karyawan_id', $myEmployeeId)->first();
        if (!$slot)
            return redirect()->back()->with('error', 'Slot tidak ditemukan.');

        $db = db_connect();
        $b = $db->table('booking b')
            ->select('b.*, u.nama AS user_nama, u.email AS user_email')
            ->join('users u', 'u.id=b.user_id', 'left')
            ->where('b.jadwal_id', $jadwalId)
            ->orderBy('b.id', 'DESC')->get()->getRowArray();

        $emp = (new EmployeeModel())->asArray()->find((int) $slot['karyawan_id']);

        $statusRaw = $b ? (string) ($b['status'] ?? '') : (string) ($slot['status'] ?? 'available');
        $status = $this->normalizeStatus($statusRaw);

        $jamStr = (string) ($slot['jam'] ?? '');
        $jamNorm = trim(str_replace(['—', '–'], '-', $jamStr)) ?: null;

        $fotoEmp = null;
        if (!empty($emp['foto']) && is_file(FCPATH . 'images/karyawan/' . $emp['foto'])) {
            $fotoEmp = $emp['foto'];
        }

        $detail = [
            'id' => $b['id'] ?? null,
            'status' => $status,
            'tanggal' => $slot['tanggal'] ?? null,
            'jam' => $jamNorm,
            'created_at' => $b['created_at'] ?? null,
            'catatan' => $b['catatan'] ?? ($b['keluhan'] ?? null),
            'user_nama' => $b['user_nama'] ?? null,
            'user_email' => $b['user_email'] ?? null,
            'jadwal_id' => (int) $slot['id'],
            'karyawan_nama' => $emp['nama'] ?? null,
            'karyawan_foto' => $fotoEmp,
            'karyawan_jabatan' => $emp['jabatan'] ?? ($emp['role'] ?? 'Pegawai'),
            'karyawan_spesialisasi' => $emp['spesialisasi'] ?? ($emp['spesialis'] ?? null),
        ];

        return view('admin/slot_detail', ['detail' => $detail]);
    }
}
