<?php
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Roles extends BaseController
{
    private array $allowedRoles = ['user', 'multiuser', 'admin'];

    private function guard(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $role = strtolower((string) session('role'));
        if ($role !== 'multiuser') {
            return redirect()->to(site_url('multiuser'))->with('error', 'Akses ditolak. Hanya Multiuser yang dapat mengelola role.');
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->guard())
            return $r;

        $q = trim((string) $this->request->getGet('q'));
        $db = db_connect();

        $empFields = array_map(static fn($f) => $f->name, $db->getFieldData('employees'));
        $joinByUserId = in_array('user_id', $empFields, true);
        $hasEmpUpdated = in_array('updated_at', $empFields, true);

        $builder = $db->table('users u');

        if ($joinByUserId) {
            $builder->join('employees e', 'e.user_id = u.id', 'left');
        } else {
            $builder->join('employees e', 'e.email = u.email', 'left');
        }

        $eUpdatedExpr = $hasEmpUpdated ? 'e.updated_at' : 'NULL';

        $builder->select("
            u.id,
            u.email,
            u.role,
            u.created_at,
            u.nama AS u_nama,
            u.updated_at AS u_updated,
            e.nama AS e_nama,
            {$eUpdatedExpr} AS e_updated,
            COALESCE(
                NULLIF(
                    CASE
                        WHEN {$eUpdatedExpr} IS NOT NULL
                             AND (u.updated_at IS NULL OR {$eUpdatedExpr} > u.updated_at)
                             AND COALESCE(e.nama,'') <> ''
                        THEN e.nama
                        ELSE u.nama
                    END, ''
                ),
                COALESCE(NULLIF(u.nama,''), NULLIF(e.nama,''), '-')
            ) AS display_name
        ");

        $builder->orderBy("
           CASE LOWER(u.role)
             WHEN 'multiuser' THEN 1
             WHEN 'admin'     THEN 2
             WHEN 'user'      THEN 3
             ELSE 4
           END
        ", '', false);
        $builder->orderBy('u.created_at', 'DESC');

        if ($q !== '') {
            $builder->groupStart()
                ->like('u.nama', $q)
                ->orLike('u.email', $q)
                ->orLike('e.nama', $q)
                ->groupEnd();
        }

        $rows = $builder->get(200)->getResultArray();

        return view('multiuser/roles_index', [
            'rows' => $rows,
            'q' => $q,
            'allowed' => $this->allowedRoles,
        ]);
    }

    public function update()
    {
        if ($r = $this->guard())
            return $r;

        $method = strtolower($this->request->getMethod());
        if ($method !== 'post') {
            return redirect()->to(site_url('multiuser/roles'))->with('error', 'Metode tidak diizinkan.');
        }

        if (
            !$this->validate([
                'user_id' => 'required|is_natural_no_zero',
                'role' => 'required|in_list[user,multiuser,admin]',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Input tidak valid.');
        }

        $targetId = (int) $this->request->getPost('user_id');
        $newRole = strtolower((string) $this->request->getPost('role'));

        $db = db_connect();
        $old = $db->table('users')->select('id, role')->where('id', $targetId)->get()->getRowArray();
        if (!$old) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }
        $oldRole = strtolower((string) ($old['role'] ?? 'user'));

        $meId = (int) (session('id') ?? session('user_id') ?? 0);
        $myRole = strtolower((string) session('role'));
        if ($targetId === $meId && $myRole === 'multiuser' && $newRole !== 'multiuser') {
            return redirect()->back()->with('warning', 'Tidak bisa menurunkan role diri sendiri.');
        }

        $now = date('Y-m-d H:i:s');
        $data = ['role' => $newRole];
        $userFields = array_map(static fn($f) => $f->name, $db->getFieldData('users'));
        if (in_array('updated_at', $userFields, true)) {
            $data['updated_at'] = $now;
        }

        $db->transStart();
        $db->table('users')->where('id', $targetId)->update($data);
        $db->transComplete();

        if (!$db->transStatus()) {
            $err = $db->error();
            $msg = $err['message'] ?? 'Gagal memperbarui role (DB error).';
            return redirect()->back()->with('error', $msg);
        }

        $check = $db->table('users')->select('role')->where('id', $targetId)->get()->getRowArray();
        $finalRole = strtolower((string) ($check['role'] ?? $oldRole));
        if ($finalRole !== $newRole) {
            return redirect()->back()->with('error', 'Role tidak berubah. Cek trigger atau hak akses DB.');
        }

        if ($targetId === $meId) {
            session()->set('role', $newRole);
        }

        return redirect()->to(site_url('multiuser/roles'))->with('success', 'Role pengguna diperbarui: ' . ucfirst($newRole));
    }

    public function json()
    {
        if ($r = $this->guard()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $rows = (new UserModel())->select('id,nama,email,role,created_at')
            ->orderBy('created_at', 'DESC')
            ->findAll(200);

        return $this->response->setJSON(['ok' => true, 'count' => count($rows), 'data' => $rows]);
    }
}
