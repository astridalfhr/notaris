<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\EmployeeResolver;
use App\Libraries\KerjaMenu;
use App\Models\EmployeeModel;

class Profile extends BaseController
{
    public function edit()
    {
        if (!session('id')) {
            return redirect()->to('/login');
        }

        // Pastikan row employees ada untuk admin ini & ambil datanya
        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $emp = (new EmployeeModel())->find($employeeId);

        $data['menu'] = KerjaMenu::get();
        return view('admin/profile_edit', ['emp' => $emp]);
    }

    public function update()
    {
        if (!session('id')) {
            return redirect()->to('/login');
        }

        // Ambil baris employee yang terkait user login
        $employeeId = EmployeeResolver::ensureForCurrentUser();

        $m = new EmployeeModel();
        $emp = $m->find($employeeId);
        if (!$emp) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');
        }

        // Ambil input; trim & normalisasi
        $nama = trim((string) $this->request->getPost('nama'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $noTelepon = trim((string) $this->request->getPost('no_telepon'));
        $jabatan = trim((string) $this->request->getPost('jabatan'));
        $deskripsi = trim((string) $this->request->getPost('deskripsi'));
        $status = (string) ($this->request->getPost('status') ?: 'aktif');

        // spesialisasi[] bisa array (checkbox) atau string CSV
        $specPost = $this->request->getPost('spesialisasi');
        $specList = [];
        if (is_array($specPost)) {
            $specList = array_values(array_filter(array_map('trim', $specPost), fn($v) => $v !== ''));
        } else {
            $csv = trim((string) $specPost);
            if ($csv !== '') {
                $specList = array_values(array_filter(array_map('trim', explode(',', $csv))));
            }
        }
        // simpan sebagai JSON supaya rapi (view lain sudah siap baca JSON/CSV)
        $spesialisasi = json_encode($specList, JSON_UNESCAPED_UNICODE);

        // Validasi minimal
        if ($nama === '' || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            return redirect()->back()->withInput()->with(
                'error',
                'Nama wajib diisi dan email harus valid.'
            );
        }

        $data = [
            'nama' => $nama,
            'email' => $email,
            'no_telepon' => $noTelepon,
            'jabatan' => $jabatan,
            'spesialisasi' => $spesialisasi,
            'deskripsi' => $deskripsi, // pastikan kolom ini ada & allowedFields
            'status' => $status,
        ];

        /** ===============================
         *  Upload foto (ke images/karyawan)
         *  =============================== */
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // (opsional) validasi mime/ukuran
            $mime = $file->getMimeType();
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) {
                return redirect()->back()->withInput()->with('error', 'Format foto harus jpg/png/webp/gif.');
            }

            $dir = FCPATH . 'images/karyawan';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $newName = $file->getRandomName();
            if ($file->move($dir, $newName)) {
                // Hapus foto lama jika ada & berbeda
                if (!empty($emp['foto']) && is_file($dir . '/' . $emp['foto']) && $emp['foto'] !== $newName) {
                    @unlink($dir . '/' . $emp['foto']);
                }
                $data['foto'] = $newName;
            } else {
                log_message('error', 'Gagal memindahkan foto profil admin.');
            }
        }

        // Simpan perubahan ke DB
        $m->update($employeeId, $data);

        // Refresh data di session (header, dsb.)
        session()->set('nama', $data['nama']);
        if ($email !== '')
            session()->set('email', $data['email']);
        if (!empty($data['foto'])) {
            session()->set('profile_photo', base_url('images/karyawan/' . $data['foto']));
        }

        $data['menu'] = KerjaMenu::get();
        return redirect()->to(site_url('layanan'))
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
