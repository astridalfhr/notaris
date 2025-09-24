<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PekerjaanModel;

class PekerjaanController extends BaseController
{
    private PekerjaanModel $M;

    public function __construct()
    {
        $this->M = new PekerjaanModel();
        helper(['form', 'text']);
    }

    /* ===== util ===== */
    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = str_replace(['–', '—'], '-', $s);
        $s = preg_replace('/[^a-z0-9\s\-]/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('/\-+/', '-', $s);
        return trim($s, '-');
    }

    /** daftar ikon (full class) */
    private function iconOptions(): array
    {
        return [
            'fa-solid fa-location-dot' => 'Location Dot',
            'fa-solid fa-map-location-dot' => 'Map Location',
            'fa-solid fa-file-shield' => 'File Shield',
            'fa-solid fa-database' => 'Database',
            'fa-solid fa-shield-halved' => 'Shield Halved',
            'fa-solid fa-magnifying-glass' => 'Magnifying Glass',
            'fa-solid fa-file-pen' => 'File Pen',
            'fa-solid fa-gift' => 'Gift',
            'fa-solid fa-layer-group' => 'Layer Group',
            'fa-solid fa-user-group' => 'User Group',
            'fa-solid fa-scissors' => 'Scissors',
            'fa-solid fa-arrow-up' => 'Arrow Up',
            'fa-solid fa-arrow-down' => 'Arrow Down',
            'fa-solid fa-seedling' => 'Seedling',
            'fa-solid fa-calendar-days' => 'Calendar Days',
            'fa-solid fa-stamp' => 'Stamp',
            'fa-solid fa-building' => 'Building',
            'fa-solid fa-briefcase' => 'Briefcase',
            'fa-solid fa-pen-to-square' => 'Pen To Square',
            'fa-solid fa-users' => 'Users',
            'fa-solid fa-store' => 'Store',
            'fa-solid fa-handshake' => 'Handshake',
            'fa-solid fa-file-contract' => 'File Contract',
            'fa-solid fa-file-lines' => 'File Lines (fallback)',
        ];
    }

    /* ===== CRUD ===== */
    public function index()
    {
        $q = trim((string) ($this->request->getGet('q') ?? ''));

        // base order; kalau kamu sudah menghapus kolom sort_order, ganti ke title:
        $builder = $this->M
            ->orderBy('category', 'ASC')
            ->orderBy('sort_order', 'ASC') // <-- kalau kolom ini dihapus, ganti ke ->orderBy('title','ASC')
            ->orderBy('id', 'ASC');

        if ($q !== '') {
            $builder = $builder->groupStart()
                ->like('title', $q)
                ->orLike('slug', $q)
                ->orLike('category', $q)
                ->orLike('icon', $q)
                ->orLike('excerpt', $q)
                ->groupEnd();
        }

        $rows = $builder->findAll();

        return view('admin/pekerjaan/index', [
            'rows' => $rows,
            'title' => 'Kelola Pekerjaan (Beranda)',
            'q' => $q, // kirim ke view biar input-nya keisi lagi
        ]);
    }

    public function create()
    {
        return view('admin/pekerjaan/form', [
            'row' => [],
            'icons' => $this->iconOptions(),
            'title' => 'Tambah Pekerjaan',
        ]);
    }

    public function store()
    {
        $data = $this->validateAndBuild();
        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse)
            return $data;

        $this->M->insert($data);
        return redirect()->to(site_url('admin/pekerjaan'))
            ->with('ok', 'Berhasil menambah.');
    }

    public function edit(int $id)
    {
        $row = $this->M->find($id);
        if (!$row)
            return redirect()->to(site_url('admin/pekerjaan'))->with('err', 'Data tidak ditemukan');

        return view('admin/pekerjaan/form', [
            'row' => $row,
            'icons' => $this->iconOptions(),
            'title' => 'Edit Pekerjaan',
        ]);
    }

    public function update(int $id)
    {
        $row = $this->M->find($id);
        if (!$row)
            return redirect()->to(site_url('admin/pekerjaan'))->with('err', 'Data tidak ditemukan');

        $data = $this->validateAndBuild($id);
        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse)
            return $data;

        $this->M->update($id, $data);
        return redirect()->to(site_url('admin/pekerjaan'))
            ->with('ok', 'Berhasil mengubah.');
    }

    public function delete(int $id)
    {
        $this->M->delete($id);
        return redirect()->to(site_url('admin/pekerjaan'))->with('ok', 'Berhasil menghapus.');
    }

    /* ===== validator ===== */
    private function validateAndBuild(?int $id = null)
    {
        $rules = [
            'category' => 'required|in_list[PPAT,NOTARIS]',
            'title' => 'required|min_length[3]|max_length[150]',
            'slug' => 'permit_empty|max_length[180]',
            'excerpt' => 'permit_empty|max_length[255]',
            'description' => 'permit_empty',
            'icon' => 'required|min_length[5]|max_length[80]',
            // 'url' => 'permit_empty|max_length[255]|valid_url_strict',
            // 'sort_order' => 'required|integer',
            'is_active' => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('err', $this->validator->listErrors());
        }

        $title = trim($this->request->getPost('title'));
        $slug = trim($this->request->getPost('slug')) ?: $this->slugify($title);

        // unik slug
        if ($this->M->slugExists($slug, $id)) {
            return redirect()->back()->withInput()
                ->with('err', 'Slug sudah dipakai. Ganti yang lain.');
        }

        return [
            'category' => strtoupper($this->request->getPost('category')),
            'title' => $title,
            'slug' => $slug,
            'excerpt' => trim((string) $this->request->getPost('excerpt')),
            'description' => trim((string) $this->request->getPost('description')),
            'icon' => trim((string) $this->request->getPost('icon')), // full class
            // 'url' => trim((string) $this->request->getPost('url')) ?: null,
            // 'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => (int) $this->request->getPost('is_active'),
        ];
    }
}
