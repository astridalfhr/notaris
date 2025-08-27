<?php
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Libraries\EmployeeResolver;
use App\Libraries\KerjaMenu;
use App\Models\EmployeeModel;

class Profile extends BaseController
{
    private function fotoUrl(?string $filename): string
    {
        if (!$filename)
            return '';
        $abs = FCPATH . 'images/karyawan/' . $filename;
        return is_file($abs) ? base_url('images/karyawan/' . $filename) : '';
    }

    public function edit()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $emp = (new EmployeeModel())->find($employeeId);

        $emp['foto_url'] = $this->fotoUrl($emp['foto'] ?? '');

        return view('multiuser/profile_edit', ['emp' => $emp, 'menu' => KerjaMenu::get()]);
    }

    public function update()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $employeeId = EmployeeResolver::ensureForCurrentUser();
        $m = new EmployeeModel();
        $emp = $m->find($employeeId);
        if (!$emp)
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan.');

        // input
        $nama = trim((string) $this->request->getPost('nama'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($nama === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Nama wajib dan email harus valid.');
        }

        $data = [
            'nama' => $nama,
            'email' => $email,
            'no_telepon' => trim((string) $this->request->getPost('no_telepon')),
            'jabatan' => trim((string) $this->request->getPost('jabatan')),
            'spesialisasi' => trim((string) $this->request->getPost('spesialisasi')),
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')),
            'status' => (string) ($this->request->getPost('status') ?: 'aktif'),
        ];

        // upload foto (ke images/karyawan)
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $file->move(FCPATH . 'images/karyawan', $file->getRandomName());
            if (!empty($emp['foto']) && is_file(FCPATH . 'images/karyawan/' . $emp['foto'])) {
                @unlink(FCPATH . 'images/karyawan/' . $emp['foto']);
            }
            $data['foto'] = $file->getName();
        }

        $m->update($employeeId, $data);

        // sinkronisasi ke session header
        session()->set('nama', $data['nama']);
        session()->set('email', $data['email']);
        if (!empty($data['foto'])) {
            session()->set('profile_photo', $this->fotoUrl($data['foto']));
        }

        return redirect()->to(site_url('multiuser'))->with('success', 'Profil berhasil diperbarui.');
    }
}