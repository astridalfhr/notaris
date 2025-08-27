<?php

namespace App\Controllers;

use App\Models\BookingModel;
use CodeIgniter\Controller;

class UserController extends Controller
{
    public function profileUser()
    {
        // Ambil data user dari session
        $session = session();
        $userId = $session->get('id'); // pastikan ini sesuai dengan session login kamu
        $userName = $session->get('name');
        $userEmail = $session->get('email');

        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil data booking user
        $bookingModel = new BookingModel();
        $bookings = $bookingModel
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Kirim ke view
        $data = [
            'title' => 'Profil User',
            'user' => [
                'name'  => $userName,
                'email' => $userEmail
            ],
            'bookings' => $bookings
        ];

        return view('profileuser', $data);
    }
}
