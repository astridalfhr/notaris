<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\BookingModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // Ambil ID & email dari session
        $userId = (int) (session('id') ?? 0);
        $sessionEmail = (string) (session('email') ?? '');

        if ($userId <= 0 && $sessionEmail === '') {
            return redirect()->to('/login');
        }

        $userModel = new UserModel();

        // Ambil user sebagai ARRAY agar akses ->asArray konsisten
        $user = null;
        if ($userId > 0) {
            $user = $userModel->asArray()
                ->select('id, nama, email, role, created_at, updated_at, profile_photo')
                ->find($userId);
        }

        // Fallback: cari berdasar email jika by id tidak ketemu
        if (!$user && $sessionEmail !== '') {
            $user = $userModel->asArray()
                ->select('id, nama, email, role, created_at, updated_at, profile_photo')
                ->where('email', $sessionEmail)
                ->first();
        }

        if (!$user) {
            return redirect()->to('/login')->with('error', 'User tidak ditemukan.');
        }

        // Ambil booking user + info pegawai & jadwal (tanggal, jam)
        $bookingModel = new BookingModel();
        $rows = $bookingModel
            ->select('
                booking.*,
                employees.nama AS nama_pegawai,
                kj.tanggal AS jadwal_tanggal,
                kj.jam      AS jadwal_jam
            ')
            ->join('employees', 'employees.id = booking.karyawan_id', 'left')
            ->join('konsultasi_jadwal kj', 'kj.id = booking.jadwal_id', 'left')
            ->where('booking.user_id', (int) $user['id'])
            ->orderBy('booking.created_at', 'DESC')
            ->findAll();

        // Normalisasi status → label + css class untuk badge
        foreach ($rows as &$r) {
            $st = strtolower(trim((string) ($r['status'] ?? '')));
            // samakan ejaan
            $st = match ($st) {
                'approve' => 'approved',
                'cancel', 'cancelled' => 'canceled',
                'done' => 'completed',
                default => $st,
            };

            [$label, $class] = match ($st) {
                'pending' => ['Menunggu Konfirmasi', 'status-pending'],
                'confirmed', 'approved' => ['Terkonfirmasi', 'status-confirmed'],
                'canceled' => ['Cancelled', 'status-cancelled'],
                'completed' => ['Selesai', 'status-completed'], // <— penting
                default => [ucfirst($st ?: '-'), 'status-unknown'],
            };

            $r['status'] = $st;
            $r['status_label'] = $label;
            $r['status_class'] = $class;
            $r['created_at_readable'] = $r['created_at'] ? date('d M Y, H:i', strtotime($r['created_at'])) : '-';
            $r['jadwal_tanggal_readable'] = $r['jadwal_tanggal'] ? date('d M Y', strtotime($r['jadwal_tanggal'])) : '-';
        }
        unset($r);

        return view('user/dashboard', [
            'title' => 'Dashboard User',
            'user' => $user,
            'bookings' => $rows,
        ]);
    }
}
