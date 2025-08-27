<?php
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;

class Employees extends BaseController
{
    public function index()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $rows = $m->orderBy('nama', 'ASC')->findAll(200);

        // Agar multiuser “luar” (bukan karyawan) tidak ikut otomatis: kita tampilkan hanya employees table.
        // (User dengan role admin/multiuser baru muncul kalau memang ada row employees-nya)
        return view('multiuser/employees_index', ['rows' => $rows]);
    }

    public function create()
    {
        if (!session('id'))
            return redirect()->to('/login');
        return view('multiuser/employees_form', ['mode' => 'create', 'row' => []]);
    }

    public function store()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $payload = [
            'user_id' => (int) $this->request->getPost('user_id') ?: null,
            'nama' => trim((string) $this->request->getPost('nama')),
            'email' => trim((string) $this->request->getPost('email')),
            'jabatan' => trim((string) $this->request->getPost('jabatan')),
            'spesialisasi' => trim((string) $this->request->getPost('spesialisasi')),
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')),
            'status' => $this->request->getPost('status') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ];

        // Upload foto (opsional)
        $img = $this->request->getFile('foto');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'images/karyawan', $newName);
            $payload['foto'] = $newName;
        }

        (new EmployeeModel())->insert($payload);
        return redirect()->to(site_url('multiuser/employees'))->with('success', 'Karyawan ditambahkan.');
    }

    public function edit(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $row = $m->find($id);
        if (!$row)
            return redirect()->to(site_url('multiuser/employees'))->with('error', 'Data tidak ditemukan.');

        // opsional: sediakan daftar user sebagai “tautan”
        $users = (new UserModel())->select('id, nama, email, role')->orderBy('nama', 'ASC')->findAll(200);

        return view('multiuser/employees_form', ['mode' => 'edit', 'row' => $row, 'users' => $users]);
    }

    public function update(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $row = $m->find($id);
        if (!$row)
            return redirect()->to(site_url('multiuser/employees'))->with('error', 'Data tidak ditemukan.');

        $payload = [
            'user_id' => (int) $this->request->getPost('user_id') ?: null,
            'nama' => trim((string) $this->request->getPost('nama')),
            'email' => trim((string) $this->request->getPost('email')),
            'jabatan' => trim((string) $this->request->getPost('jabatan')),
            'spesialisasi' => trim((string) $this->request->getPost('spesialisasi')),
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')),
            'status' => $this->request->getPost('status') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ];

        $img = $this->request->getFile('foto');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'images/karyawan', $newName);
            $payload['foto'] = $newName;
        }

        $m->update($id, $payload);
        return redirect()->to(site_url('multiuser/employees'))->with('success', 'Data karyawan diperbarui.');
    }

    public function toggle(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $row = $m->find($id);
        if ($row) {
            $m->update($id, ['status' => $row['status'] === 'aktif' ? 'nonaktif' : 'aktif']);
        }
        return redirect()->to(site_url('multiuser/employees'));
    }

    public function delete(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        (new EmployeeModel())->delete($id);
        return redirect()->to(site_url('multiuser/employees'))->with('success', 'Karyawan dihapus.');
    }
}
