<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\JadwalModel;
use App\Models\EmployeeModel;
use App\Libraries\EmployeeResolver;
use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class Booking extends Controller
{
    public function create()
    {
        helper(['form']);
        if (!session('id')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dulu.');
        }

        $karyawan_id = (int) $this->request->getGet('karyawan');
        $jadwal_id = (int) $this->request->getGet('jadwal');
        if (!$karyawan_id || !$jadwal_id) {
            return redirect()->to('/layanan')->with('error', 'Data booking tidak lengkap.');
        }

        $employeeModel = new EmployeeModel();
        $karyawan = $employeeModel->asArray()->find($karyawan_id);
        if (!$karyawan) {
            return redirect()->to('/layanan')->with('error', 'Karyawan tidak ditemukan.');
        }

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->asArray()->find($jadwal_id);
        if (!$jadwal) {
            return redirect()->to('/layanan')->with('error', 'Jadwal tidak ditemukan.');
        }
        if (isset($jadwal['karyawan_id']) && (int) $jadwal['karyawan_id'] !== $karyawan_id) {
            return redirect()->to('/layanan')->with('error', 'Jadwal tidak sesuai dengan karyawan.');
        }

        $namaUser = (string) (session('nama') ?? '');
        $emailUser = (string) (session('email') ?? '');
        $jam = $jadwal['jam'] ?? trim(($jadwal['jam_mulai'] ?? '') . ' - ' . ($jadwal['jam_selesai'] ?? ''), ' -');

        return view('booking/form', [
            'karyawan_id' => $karyawan_id,
            'jadwal_id' => $jadwal_id,
            'nama_karyawan' => $karyawan['nama'] ?? '',
            'tanggal' => $jadwal['tanggal'] ?? null,
            'jam' => $jam,
            'nama_user' => $namaUser,
            'email_user' => $emailUser,
        ]);
    }

    public function store()
    {
        if (!session('id')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dulu.');
        }

        $bookingModel = new BookingModel();
        $jadwalModel = new JadwalModel();

        $karyawanId = (int) $this->request->getPost('karyawan_id');
        $jadwalId = (int) $this->request->getPost('jadwal_id');
        $jam = (string) $this->request->getPost('jam');
        $catatan = (string) $this->request->getPost('catatan');
        $noTelp = (string) $this->request->getPost('no_telepon');

        if (!$karyawanId || !$jadwalId || $jam === '') {
            return redirect()->back()->withInput()->with('error', 'Data form tidak lengkap.');
        }

        $jadwal = $jadwalModel->find($jadwalId);
        if (!$jadwal) {
            return redirect()->back()->withInput()->with('error', 'Jadwal tidak valid.');
        }
        if (isset($jadwal['karyawan_id']) && (int) $jadwal['karyawan_id'] !== $karyawanId) {
            return redirect()->back()->withInput()->with('error', 'Jadwal tidak sesuai dengan karyawan.');
        }

        $data = [
            'user_id' => (int) session('id'),
            'karyawan_id' => $karyawanId,
            'jadwal_id' => $jadwalId,
            'nama' => session('nama') ?? '',
            'email' => session('email') ?? '',
            'no_telepon' => $noTelp ?: null,
            'jam' => $jam,
            'catatan' => $catatan,
            'status' => 'pending',
        ];

        $db = db_connect();
        $db->transStart();
        $bookingModel->insert($data);
        $jadwalModel->update($jadwalId, ['status' => 'booked']);
        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan booking.');
        }

        return redirect()->to('/layanan')->with('success', 'Booking berhasil dibuat.');
    }

    public function detail($id)
    {
        $id = (int) $id;
        if (!$id) {
            return redirect()->to('/user/dashboard');
        }

        $userId = (int) (session('id') ?? 0);
        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dulu.');
        }

        $db = db_connect();
        $detail = $db->table('booking b')
            ->select("
                b.id, b.user_id, b.karyawan_id, b.jadwal_id,
                kj.jam, b.status, b.catatan, b.no_telepon,
                b.created_at, b.updated_at,
                kj.tanggal,
                u.nama AS user_nama, u.email AS user_email,
                e.nama AS karyawan_nama, e.jabatan AS karyawan_jabatan, e.spesialisasi AS karyawan_spesialisasi, e.foto AS karyawan_foto
            ")
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->join('users u', 'u.id = b.user_id', 'left')
            ->join('employees e', 'e.id = b.karyawan_id', 'left')
            ->where('b.id', $id)
            ->get()->getRowArray();

        if (!$detail) {
            throw new PageNotFoundException('Booking tidak ditemukan');
        }

        $role = strtolower((string) (session('role') ?? 'user'));
        $isAdmin = ($role === 'admin');
        $isMultiuser = in_array($role, ['multiuser', 'multi-user'], true);
        $isEmployee = in_array($role, ['karyawan', 'pegawai', 'employee', 'staff'], true);
        $ownsAsUser = ((int) $detail['user_id'] === $userId);

        $ownsAsEmployee = false;
        if ($isEmployee || $isMultiuser) {
            $myEmpId = (int) EmployeeResolver::ensureForCurrentUser();
            $ownsAsEmployee = $myEmpId && ((int) $detail['karyawan_id'] === $myEmpId);
        }

        if (!($isAdmin || $isMultiuser || $ownsAsEmployee || $ownsAsUser)) {
            return redirect()->to('/unauthorized');
        }

        return view('booking/detail', ['detail' => $detail]);
    }

    public function cancel($id)
    {
        if (!session('id')) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dulu.');
        }

        $id = (int) $id;
        $bookingModel = new BookingModel();
        $jadwalModel = new JadwalModel();

        $row = $bookingModel->select('id, user_id, jadwal_id, status')->find($id);
        if (!$row) {
            return redirect()->back()->with('error', 'Booking tidak ditemukan.');
        }

        $isAdmin = strtolower((string) (session('role') ?? '')) === 'admin';
        if (!$isAdmin && (int) $row['user_id'] !== (int) session('id')) {
            return redirect()->back()->with('error', 'Anda tidak berhak membatalkan booking ini.');
        }

        if (strtolower((string) $row['status']) === 'cancelled') {
            return redirect()->back()->with('info', 'Booking sudah dibatalkan sebelumnya.');
        }

        $db = db_connect();
        $db->transStart();
        $bookingModel->update($row['id'], ['status' => 'cancelled']);
        if (!empty($row['jadwal_id'])) {
            $jadwalModel->update($row['jadwal_id'], ['status' => 'available']);
        }
        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->with('error', 'Gagal membatalkan booking.');
        }

        return redirect()->to('/user/dashboard')->with('success', 'Booking berhasil dibatalkan.');
    }
}
