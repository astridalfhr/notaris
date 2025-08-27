<?php // app/Controllers/Multiuser/Homepage.php
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;

class Homepage extends BaseController
{
    public function index()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new SiteSettingsModel();
        $row = $m->first();
        if (!$row) {
            $m->insert([]);
            $row = $m->first();
        }

        return view('multiuser/homepage_form', ['data' => $row]);
    }

    public function save()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new SiteSettingsModel();
        $id = (int) ($this->request->getPost('id') ?? 0);

        $payload = [
            'owner_name' => trim((string) $this->request->getPost('owner_name')),
            'owner_subtitle' => trim((string) $this->request->getPost('owner_subtitle')),
            'about_title' => trim((string) $this->request->getPost('about_title')),
            'about_body' => (string) $this->request->getPost('about_body'),
            'hero_title' => trim((string) $this->request->getPost('hero_title')),
            'hero_tagline' => trim((string) $this->request->getPost('hero_tagline')),
            'company_name' => trim((string) $this->request->getPost('company_name')),
            'company_info' => (string) $this->request->getPost('company_info'),
            'social_instagram' => trim((string) $this->request->getPost('social_instagram')),
            'social_whatsapp' => trim((string) $this->request->getPost('social_whatsapp')),
            'social_email' => trim((string) $this->request->getPost('social_email')),
            'social_linkedin' => trim((string) $this->request->getPost('social_linkedin')),
            'address' => trim((string) $this->request->getPost('address')),
            'map_embed' => (string) $this->request->getPost('map_embed'),
        ];

        // upload foto pemilik (opsional)
        $file = $this->request->getFile('owner_photo');
        if ($file && $file->isValid()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'images/owner', $newName);
            $payload['owner_photo'] = $newName;
        }

        if ($id > 0)
            $m->update($id, $payload);
        else {
            $first = $m->first();
            if ($first)
                $m->update($first['id'], $payload);
            else
                $m->insert($payload);
        }

        return redirect()->to(site_url('multiuser/homepage'))->with('success', 'Pengaturan beranda tersimpan.');
    }
}
