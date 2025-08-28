<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Google_Client;
use Google_Service_Oauth2;

class Auth extends BaseController
{
    // === Helper untuk membuat Google Client ===
    private function googleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));

        // Redirect URI dari ENV; kalau kosong, fallback ke baseURL/auth/callback
        $redirect = getenv('GOOGLE_REDIRECT_URI');
        if (!$redirect) {
            $base = rtrim(config('App')->baseURL, '/');
            $redirect = $base . '/auth/callback';
        }
        $client->setRedirectUri($redirect);

        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes(['email', 'profile']);

        log_message('debug', 'Using Google redirect URI: ' . $redirect);
        return $client;
    }

    // === Halaman login biasa (opsional) ===
    public function login()
    {
        return view('auth/login'); // pastikan view ada; tombol "Login dengan Google" mengarah ke site_url('auth/google')
    }

    // === Start OAuth (klik tombol) ===
    public function google()
    {
        $client = $this->googleClient();
        return redirect()->to($client->createAuthUrl());
    }

    // === Callback dari Google ===
    public function googleCallback()
    {
        $client = $this->googleClient();

        $code = $this->request->getGet('code');
        if (!$code) {
            return redirect()->to('/login')->with('error', 'Kode OAuth tidak ditemukan.');
        }

        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            log_message('error', 'OAuth error: ' . ($token['error_description'] ?? $token['error']));
            return redirect()->to('/login')->with('error', 'Login Google gagal.');
        }

        $client->setAccessToken($token);
        $service = new Google_Service_Oauth2($client);
        $me = $service->userinfo->get();

        // ====== SESUAIKAN DENGAN SISTEMMU ======
        // Contoh minimal: simpan ke session. Di real app, cocokkan/buat user di DB dulu.
        $userData = [
            'id' => (string) $me->id,
            'name' => (string) $me->name,
            'email' => (string) $me->email,
            'avatar' => (string) $me->picture,
            'provider' => 'google',
            'logged_in' => true,
        ];
        session()->set($userData);

        // arahkan ke dashboard/home sesuai app-mu
        return redirect()->to('/');
    }

    // === Logout ===
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda telah keluar.');
    }

    // === Endpoint debug (opsional, hapus di production) ===
    public function debugOAuth()
    {
        $base = rtrim(config('App')->baseURL, '/');
        $envRedirect = getenv('GOOGLE_REDIRECT_URI') ?: '';
        $used = $envRedirect ?: ($base . '/auth/callback');

        return $this->response->setJSON([
            'APP_BASE_URL' => $base,
            'GOOGLE_REDIRECT_URI' => $envRedirect,
            'redirect_used' => $used,
        ]);
    }
}
