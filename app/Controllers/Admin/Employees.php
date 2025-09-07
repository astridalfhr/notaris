<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use App\Models\UserModel;

class Employees extends BaseController
{
    /** Folder penyimpanan foto relatif dari FCPATH */
    private string $photoDir = 'images/karyawan';

    /* -------------------- Helpers private -------------------- */

    private function normalizeSpecs($postValue): string
    {
        $arr = [];
        if (is_array($postValue)) {
            $arr = $postValue;
        } elseif (is_string($postValue) && $postValue !== '') {
            $arr = explode(',', $postValue); // dukung CSV lama
        }
        $arr = array_values(array_unique(array_filter(array_map(
            fn($v) => trim((string) $v),
            $arr
        ))));

        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    private function ensurePhotoDir(): string
    {
        $abs = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->photoDir;
        if (!is_dir($abs)) {
            @mkdir($abs, 0775, true);
        }
        return $abs;
    }

    private function movePhoto(\CodeIgniter\HTTP\Files\UploadedFile $file, ?string $oldFilename = null): ?string
    {
        if (!$file || !$file->isValid())
            return null;

        $this->ensurePhotoDir();
        $newName = $file->getRandomName();
        if (!$file->hasMoved()) {
            $file->move(FCPATH . $this->photoDir, $newName);
        }

        if ($oldFilename) {
            $oldPath = FCPATH . $this->photoDir . DIRECTORY_SEPARATOR . $oldFilename;
            if (is_file($oldPath))
                @unlink($oldPath);
        }

        return $newName;
    }

    /* -------------------- Actions -------------------- */

    public function index()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $rows = $m->orderBy('nama', 'ASC')->findAll(200);

        return view('admin/employees_index', ['rows' => $rows]);
    }

    public function create()
    {
        if (!session('id'))
            return redirect()->to('/login');

        return view('admin/employees_form', ['mode' => 'create', 'row' => []]);
    }

    public function store()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $specJson = $this->normalizeSpecs($this->request->getPost('spesialisasi'));
        if (json_decode($specJson, true) === []) {
            return redirect()->back()->withInput()->with('error', 'Minimal 1 spesialisasi harus dipilih.');
        }

        $payload = [
            'user_id' => ($uid = (int) $this->request->getPost('user_id')) ?: null,
            'nama' => trim((string) $this->request->getPost('nama')),
            'email' => trim((string) $this->request->getPost('email')),
            'jabatan' => trim((string) $this->request->getPost('jabatan')),
            'spesialisasi' => $specJson, // JSON
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')),
            'status' => $this->request->getPost('status') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ];

        // Upload foto (opsional)
        $img = $this->request->getFile('foto');
        if ($img && $img->isValid()) {
            $newName = $this->movePhoto($img);
            if ($newName)
                $payload['foto'] = $newName;
        }

        (new EmployeeModel())->insert($payload);
        return redirect()->to(site_url('admin/employees'))->with('success', 'Karyawan ditambahkan.');
    }

    public function edit(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $row = $m->find($id);
        if (!$row) {
            return redirect()->to(site_url('admin/employees'))->with('error', 'Data tidak ditemukan.');
        }

        $users = (new UserModel())->select('id, nama, email, role')->orderBy('nama', 'ASC')->findAll(200);

        return view('admin/employees_form', [
            'mode' => 'edit',
            'row' => $row,
            'users' => $users,
        ]);
    }

    public function update(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $row = $m->find($id);
        if (!$row) {
            return redirect()->to(site_url('admin/employees'))->with('error', 'Data tidak ditemukan.');
        }

        $specJson = $this->normalizeSpecs($this->request->getPost('spesialisasi'));
        if (json_decode($specJson, true) === []) {
            return redirect()->back()->withInput()->with('error', 'Minimal 1 spesialisasi harus dipilih.');
        }

        $payload = [
            'user_id' => ($uid = (int) $this->request->getPost('user_id')) ?: null,
            'nama' => trim((string) $this->request->getPost('nama')),
            'email' => trim((string) $this->request->getPost('email')),
            'jabatan' => trim((string) $this->request->getPost('jabatan')),
            'spesialisasi' => $specJson, // JSON
            'deskripsi' => trim((string) $this->request->getPost('deskripsi')),
            'status' => $this->request->getPost('status') === 'nonaktif' ? 'nonaktif' : 'aktif',
        ];

        $img = $this->request->getFile('foto');
        if ($img && $img->isValid()) {
            $newName = $this->movePhoto($img, $row['foto'] ?? null);
            if ($newName)
                $payload['foto'] = $newName;
        }

        $m->update($id, $payload);
        return redirect()->to(site_url('admin/employees'))->with('success', 'Data karyawan diperbarui.');
    }

    public function toggle(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $row = $m->find($id);
        if ($row) {
            $m->update($id, ['status' => ($row['status'] === 'aktif') ? 'nonaktif' : 'aktif']);
        }
        return redirect()->to(site_url('admin/employees'));
    }

    public function delete(int $id)
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new EmployeeModel();
        $row = $m->find($id);
        if ($row) {
            if (!empty($row['foto'])) {
                $path = FCPATH . $this->photoDir . DIRECTORY_SEPARATOR . $row['foto'];
                if (is_file($path))
                    @unlink($path);
            }
            $m->delete($id);
        }

        return redirect()->to(site_url('admin/employees'))->with('success', 'Karyawan dihapus.');
    }
}
