<?php
namespace App\Controllers\Multiuser;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;

class Company extends BaseController
{
    public function index()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new SiteSettingsModel();
        $row = $m->first() ?? [];

        return view('multiuser/company_form', ['row' => $row]);
    }

    public function save()
    {
        if (!session('id'))
            return redirect()->to('/login');

        $m = new SiteSettingsModel();
        $row = $m->first();

        $payload = [
            'company_name' => trim((string) $this->request->getPost('company_name')),
            'company_info' => trim((string) $this->request->getPost('company_info')),
            'owner_name' => trim((string) $this->request->getPost('owner_name')),
            'owner_subtitle' => trim((string) $this->request->getPost('owner_subtitle')),
            'social_email' => trim((string) $this->request->getPost('social_email')),
            'social_instagram' => trim((string) $this->request->getPost('social_instagram')),
            'social_whatsapp' => trim((string) $this->request->getPost('social_whatsapp')),
            'social_linkedin' => trim((string) $this->request->getPost('social_linkedin')),
            'address' => trim((string) $this->request->getPost('address')),
            'map_embed' => trim((string) $this->request->getPost('map_embed')),
        ];

        // upload foto owner (opsional)
        $img = $this->request->getFile('owner_photo');
        if ($img && $img->isValid()) {
            $newName = $img->getRandomName();
            $img->move(FCPATH . 'images/owner', $newName);
            $payload['owner_photo'] = $newName;
        }

        if ($row)
            $m->update($row['id'], $payload);
        else
            $m->insert($payload);

        return redirect()->to(site_url('multiuser/company'))->with('success', 'Profil perusahaan disimpan.');
    }
}
