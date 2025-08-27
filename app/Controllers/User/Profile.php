<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profile extends BaseController
{
    public function edit()
    {
        $userId = session('id');
        if (!$userId)
            return redirect()->to('/login');

        $user = (new UserModel())->asArray()
            ->select('id, nama, email, role, profile_photo')
            ->find($userId);

        if (!$user)
            return redirect()->to('/login');

        return view('user/edit_profile', ['user' => $user]);
    }

    public function update()
    {
        $userId = session('id');
        if (!$userId)
            return redirect()->to('/login');

        $userM = new UserModel();
        $user = $userM->asArray()->find($userId);
        if (!$user)
            return redirect()->to('/login');

        // Validasi sederhana
        $rules = [
            'nama' => 'required|min_length[3]',
            'email' => 'required|valid_email',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'email' => strtolower($this->request->getPost('email')),
        ];

        $dir = FCPATH . 'uploads/profiles';
        $old = $user['profile_photo'] ?? '';
        $oldIsUrl = $old && filter_var($old, FILTER_VALIDATE_URL);

        // ====== 1) HAPUS FOTO? ======
        if ($this->request->getPost('remove_photo') === '1') {
            // hapus file lama jika lokal
            if ($old && !$oldIsUrl) {
                $oldPath = $dir . '/' . $old;
                if (is_file($oldPath))
                    @unlink($oldPath);
            }
            $data['profile_photo'] = null;

            // **sinkronkan session**
            session()->set(['profile_photo' => null]);
        }
        // ====== 2) UPLOAD FOTO BARU? (hanya jika tidak minta hapus) ======
        else {
            $file = $this->request->getFile('photo');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $ext = strtolower($file->getExtension());
                $mime = $file->getMimeType();
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed) || !str_starts_with($mime, 'image/')) {
                    return redirect()->back()->withInput()->with('error', 'Format gambar tidak didukung.');
                }

                // pastikan folder ada
                if (!is_dir($dir))
                    @mkdir($dir, 0775, true);

                // nama unik & pindahkan
                $newName = 'u' . $userId . '_' . time() . '.' . $ext;
                $file->move($dir, $newName);

                // hapus file lama jika lokal
                if ($old && !$oldIsUrl) {
                    $oldPath = $dir . '/' . $old;
                    if (is_file($oldPath))
                        @unlink($oldPath);
                }

                $data['profile_photo'] = $newName;

                // **sinkronkan session** + bust cache di navbar (query ?v=)
                session()->set(['profile_photo' => $newName]);
            }
        }

        // Update DB
        $userM->update($userId, $data);

        // Sinkronkan session nama/email juga
        session()->set([
            'nama' => $data['nama'],
            'email' => $data['email'],
        ]);

        return redirect()->to('/user/dashboard')->with('success', 'Profil berhasil diperbarui.');
    }
}
