<?php
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Models\SiteHeroModel;

class Hero extends BaseController
{
    public function index()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new SiteHeroModel();
        $rows = $m->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll(50);
        return view('multiuser/hero_index', ['rows' => $rows]);
    }

    public function create()
    {
        return view('multiuser/hero_form', ['mode' => 'create', 'row' => []]);
    }

    public function store()
    {
        $m = new SiteHeroModel();
        $payload = [
            'title' => trim((string) $this->request->getPost('title')),
            'tagline' => trim((string) $this->request->getPost('tagline')),
            'button_text' => trim((string) $this->request->getPost('button_text')),
            'button_link' => trim((string) $this->request->getPost('button_link')),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => (int) ($this->request->getPost('is_active') ? 1 : 0),
        ];

        // upload image (opsional)
        $img = $this->request->getFile('image');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'images/hero', $newName);
            $payload['image'] = $newName;
        }

        $m->insert($payload);
        return redirect()->to(site_url('multiuser/hero'))->with('success', 'Banner ditambahkan.');
    }

    public function edit(int $id)
    {
        $m = new SiteHeroModel();
        $row = $m->find($id);
        if (!$row)
            return redirect()->to(site_url('multiuser/hero'))->with('error', 'Banner tidak ditemukan.');
        return view('multiuser/hero_form', ['mode' => 'edit', 'row' => $row]);
    }

    public function update(int $id)
    {
        $m = new SiteHeroModel();
        $row = $m->find($id);
        if (!$row)
            return redirect()->to(site_url('multiuser/hero'))->with('error', 'Banner tidak ditemukan.');

        $payload = [
            'title' => trim((string) $this->request->getPost('title')),
            'tagline' => trim((string) $this->request->getPost('tagline')),
            'button_text' => trim((string) $this->request->getPost('button_text')),
            'button_link' => trim((string) $this->request->getPost('button_link')),
            'sort_order' => (int) $this->request->getPost('sort_order'),
            'is_active' => (int) ($this->request->getPost('is_active') ? 1 : 0),
        ];

        $img = $this->request->getFile('image');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'images/hero', $newName);
            $payload['image'] = $newName;
        }

        $m->update($id, $payload);
        return redirect()->to(site_url('multiuser/hero'))->with('success', 'Banner diperbarui.');
    }

    public function delete(int $id)
    {
        $m = new SiteHeroModel();
        $m->delete($id);
        return redirect()->to(site_url('multiuser/hero'))->with('success', 'Banner dihapus.');
    }

    public function toggle(int $id)
    {
        $m = new SiteHeroModel();
        $row = $m->find($id);
        if ($row)
            $m->update($id, ['is_active' => $row['is_active'] ? 0 : 1]);
        return redirect()->to(site_url('multiuser/hero'));
    }

    public function moveUp(int $id)
    {
        $m = new SiteHeroModel();
        $row = $m->find($id);
        if ($row) {
            $row['sort_order'] = max(0, (int) $row['sort_order'] - 1);
            $m->update($id, ['sort_order' => $row['sort_order']]);
        }
        return redirect()->to(site_url('multiuser/hero'));
    }

    public function moveDown(int $id)
    {
        $m = new SiteHeroModel();
        $row = $m->find($id);
        if ($row) {
            $row['sort_order'] = (int) $row['sort_order'] + 1;
            $m->update($id, ['sort_order' => $row['sort_order']]);
        }
        return redirect()->to(site_url('multiuser/hero'));
    }
}
