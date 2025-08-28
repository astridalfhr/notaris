<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\EmployeeModel;
use CodeIgniter\Controller;
use CodeIgniter\I18n\Time;
use Google_Client;
use Google_Service_Oauth2;
use App\Libraries\EmployeeResolver;

class Auth extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    public function register()
    {
        return view('auth/register');
    }

    public function forgot()
    {
        return view('auth/forgot');
    }

    private function canonRole(string $v): string
    {
        $k = strtolower(preg_replace('/[^a-z]/', '', $v));
        if (in_array($k, ['admin', 'superadmin', 'staff', 'employee', 'pegawai', 'karyawan'], true))
            return 'admin';
        if (in_array($k, ['multi', 'multiuser'], true))
            return 'multiuser';
        return $k === '' ? 'user' : $k;
    }

    private function setUserSession(array $user): void
    {
        $id = $user['id'] ?? null;
        $nama = trim((string) ($user['nama'] ?? ($user['name'] ?? '')));
        $email = strtolower((string) ($user['email'] ?? ''));
        $role = $this->canonRole((string) ($user['role'] ?? 'user'));
        $photo = $user['profile_photo'] ?? null;

        session()->regenerate(true);
        session()->set([
            'id' => $id,
            'nama' => $nama,
            'email' => $email,
            'role' => $role,
            'profile_photo' => $photo,
            'logged_in' => true,
            'user_id' => $id,
            'user_name' => $nama,
            'user_email' => $email,
        ]);

        if ($role === 'admin' || $role === 'super-admin') {
            try {
                $employeeId = EmployeeResolver::ensureForCurrentUser();
                session()->set('employee_id', $employeeId);
                $emp = (new EmployeeModel())->find($employeeId);
                if ($emp && !empty($emp['nama'])) {
                    session()->set('nama', (string) $emp['nama']);
                    session()->set('user_name', (string) $emp['nama']);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Ensure employee failed: ' . $e->getMessage());
                session()->setFlashdata('warning', 'Data pegawai admin belum lengkap: ' . $e->getMessage());
            }
        }
    }

    private function afterLoginRedirect(): string
    {
        $role = (string) (session('role') ?? 'user');
        return match ($role) {
            'admin' => '/admin',
            'multiuser' => '/multiuser',
            default => '/user',
        };
    }

    // ====== helper untuk hitung Redirect URI yang konsisten ======
    private function resolveRedirectUri(): string
    {
        $env = (string) (getenv('GOOGLE_REDIRECT_URI') ?: '');
        if ($env !== '')
            return $env;

        $appBase = (string) (getenv('APP_BASE_URL') ?: '');
        if ($appBase !== '')
            return rtrim($appBase, '/') . '/auth/callback';

        $base = rtrim((string) config('App')->baseURL, '/');
        return $base . '/auth/callback';
    }

    public function manual_login()
    {
        $throttler = service('throttler');
        $ip = (string) $this->request->getIPAddress();
        $ua = (string) ($this->request->getUserAgent() ?? '');
        $key = 'login-' . md5($ip . '|' . $ua);
        if ($throttler->check($key, 5, MINUTE) === false) {
            return redirect()->back()->withInput()->with('error', 'Terlalu banyak percobaan. Coba lagi sesaat.');
        }
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $email = strtolower((string) $this->request->getPost('email'));
        $pass = (string) $this->request->getPost('password');

        $userM = new UserModel();
        $user = $userM->findByEmail($email);
        if (!$user || empty($user['password']) || !password_verify($pass, $user['password'])) {
            return redirect()->back()->with('error', 'Email atau password salah.');
        }
        $this->setUserSession($user);
        return redirect()->to($this->afterLoginRedirect());
    }

    public function registerPost()
    {
        $rules = [
            'nama' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
        ];
        $messages = [
            'email' => ['is_unique' => 'Email sudah terdaftar.'],
            'password_confirm' => ['matches' => 'Konfirmasi password tidak sama.']
        ];
        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        (new UserModel())->insert([
            'nama' => (string) $this->request->getPost('nama'),
            'email' => strtolower((string) $this->request->getPost('email')),
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => 'user',
        ]);
        return redirect()->to('/login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function forgotPost()
    {
        $email = strtolower((string) $this->request->getPost('email'));
        $userM = new UserModel();
        $user = $userM->findByEmail($email);
        if ($user) {
            helper('text');
            $token = random_string('alnum', 40);
            $userM->update($user['id'], [
                'reset_token' => $token,
                'reset_expires' => Time::now()->addMinutes(30),
            ]);
            $resetUrl = base_url('auth/reset?token=' . $token);
            $emailServer = \Config\Services::email();
            $emailServer->setTo($email);
            $emailServer->setFrom('ragilibnuhajarMkn@gmail.com', 'Notariss System');
            $emailServer->setSubject('Reset Password Anda');
            $emailServer->setMessage(
                "Halo {$user['nama']},<br><br>" .
                "Kami menerima permintaan reset password.<br>" .
                "Silakan klik tautan berikut untuk mengatur password baru:<br>" .
                "<a href='{$resetUrl}'>{$resetUrl}</a><br><br>" .
                "Tautan ini berlaku selama 30 menit.<br><br>" .
                "Salam,<br>Tim Notariss"
            );
            if (!$emailServer->send()) {
                $debug = $emailServer->printDebugger(['headers', 'subject', 'body']);
                log_message('error', 'Reset password email failed: ' . $debug);
            }
        }
        return redirect()->back()->with('success', 'Jika email terdaftar, tautan reset telah dikirim.');
    }

    public function reset()
    {
        $token = (string) $this->request->getGet('token');
        if ($token === '') {
            return redirect()->to('/forgot')->with('error', 'Token tidak valid.');
        }
        return view('auth/reset', ['token' => $token]);
    }

    public function resetPost()
    {
        $rules = [
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]'
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $token = (string) $this->request->getPost('token');
        $userM = new UserModel();
        $user = $userM->where('reset_token', $token)
            ->where('reset_expires >=', Time::now()->toDateTimeString())
            ->first();
        if (!$user) {
            return redirect()->to('/forgot')->with('error', 'Token kedaluwarsa/invalid.');
        }
        $userM->update($user['id'], [
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null,
        ]);
        return redirect()->to('/login')->with('success', 'Password diperbarui. Silakan login.');
    }

    // ====== GOOGLE OAUTH ======

    // kompatibel dengan path lama: /auth/LoginWithGoogle
    public function LoginWithGoogle()
    {
        $client = new Google_Client();
        $client->setClientId((string) getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret((string) getenv('GOOGLE_CLIENT_SECRET'));

        $redirect = $this->resolveRedirectUri(); // **pakai helper konsisten**
        $client->setRedirectUri($redirect);

        $client->addScope('email');
        $client->addScope('profile');

        log_message('debug', 'Using Google redirect URI: ' . $redirect);
        return redirect()->to($client->createAuthUrl());
    }

    // kompatibel dengan path lama: /auth/googleCallback
    public function googleCallback()
    {
        $code = (string) $this->request->getGet('code');
        if ($code === '') {
            return redirect()->to('/login')->with('error', 'Login Google gagal: kode tidak ditemukan.');
        }

        $client = new Google_Client();
        $client->setClientId((string) getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret((string) getenv('GOOGLE_CLIENT_SECRET'));

        $redirect = $this->resolveRedirectUri(); // **pakai helper konsisten**
        $client->setRedirectUri($redirect);

        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error']) || empty($token['access_token'])) {
            $msg = $token['error_description'] ?? $token['error'] ?? 'Token exchange error';
            log_message('error', 'OAuth error: ' . $msg);
            return redirect()->to('/login')->with('error', 'Login Google gagal: ' . $msg);
        }

        $client->setAccessToken($token['access_token']);
        $gs = new Google_Service_Oauth2($client);
        $g = $gs->userinfo->get();

        $userM = new UserModel();
        $email = strtolower((string) ($g->email ?? ''));
        $photo = (string) ($g->picture ?? '');
        $name = (string) ($g->name ?? '');
        $gid = (string) ($g->id ?? '');

        $user = $userM->asArray()->where('email', $email)->first();
        if (!$user) {
            $userId = $userM->insert([
                'nama' => $name,
                'email' => $email,
                'google_id' => $gid,
                'role' => 'user',
                'profile_photo' => $photo ?: null,
            ]);
            $user = $userM->asArray()->find($userId);
        } else {
            $update = [];
            if (empty($user['google_id']) && $gid !== '')
                $update['google_id'] = $gid;
            if (empty($user['profile_photo']) && $photo !== '')
                $update['profile_photo'] = $photo;
            if ($update) {
                $userM->update($user['id'], $update);
                $user = $userM->asArray()->find($user['id']);
            }
        }

        $this->setUserSession($user);
        return redirect()->to($this->afterLoginRedirect());
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Berhasil logout.');
    }
}
