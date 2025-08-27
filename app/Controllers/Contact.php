<?php

namespace App\Controllers;

use App\Models\ContactModel;
use CodeIgniter\Controller;
use Config\Services;

class Contact extends Controller
{
    protected string $emailTo;
    protected string $waDigits;   
    protected string $address;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->emailTo = getenv('CONTACT_NOTARIS_EMAIL') ?: 'ragilibnuhajarMkn@gmail.com';
        $this->waDigits = preg_replace('/\D+/', '', getenv('CONTACT_WHATSAPP') ?: '6285271288009'); 
        $this->address = getenv('CONTACT_OFFICE_ADDRESS') ?: 'Jl. Akasia No.88, Pangkalan Kerinci Bar., Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654';
    }

    public function index()
    {
        $waText = 'Halo Notaris Pelalawan, saya ingin konsultasi.';
        $data = [
            'title' => 'Kontak Notaris Pelalawan',
            'errors' => session()->getFlashdata('errors') ?? [],
            // links untuk fitur klik
            'waLink' => 'https://wa.me/' . $this->waDigits . '?text=' . rawurlencode($waText),
            'mailtoLink' => 'mailto:' . $this->emailTo . '?subject=' . rawurlencode('Konsultasi Notaris') . '&body=' . rawurlencode("Halo Notaris Pelalawan,\n\nSaya ingin konsultasi terkait ....\n\nTerima kasih."),
            'mapsLink' => 'https://www.google.com/maps?q=Jl.%20Akasia%20(Ujung)%20Nomor%2088,%20Pangkalan%20Kerinci,%20Kabupaten%20Pelalawan,%20Riau&output=embed' . rawurlencode($this->address),
            'layananUrl' => site_url('layanan'),
            // tampilkan info
            'notarisEmail' => $this->emailTo,
            'hotline' => '+62 852-7128-8009',
            'address' => $this->address,
        ];

        return view('kontak', $data);
    }

    public function send()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'phone' => 'permit_empty|regex_match[/^\+?\d{7,15}$/]',
            'service' => 'permit_empty|max_length[100]',
            'message' => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $model = new ContactModel();

        // Simpan ke DB
        $model->insert([
            'name' => $post['name'],
            'email' => $post['email'],
            'phone' => $post['phone'] ?? null,
            'service' => $post['service'] ?? null,
            'message' => $post['message'],
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 255),
            'status' => 'new',
        ]);

        // Kirim email
        $email = Services::email();
        $email->setFrom(getenv('MAIL_FROM_EMAIL') ?: 'no-reply@localhost', getenv('MAIL_FROM') ?: 'Website Notaris');
        $email->setTo($this->emailTo);
        $email->setSubject('[Kontak] ' . $post['name'] . ' - ' . ($post['service'] ?? ''));
        $body = "
            <h3>Pesan Kontak Baru</h3>
            <p><b>Nama:</b> " . esc($post['name']) . "</p>
            <p><b>Email:</b> " . esc($post['email']) . "</p>
            <p><b>Telepon:</b> " . esc($post['phone']) . "</p>
            <p><b>Layanan:</b> " . esc($post['service'] ?? '') . "</p>
            <p><b>Pesan:</b><br>" . nl2br(esc($post['message'])) . "</p>
        ";
        $email->setMessage($body);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', 'Email gagal terkirim: ' . print_r($email->printDebugger(['headers']), true));
            return redirect()->back()->withInput()->with('error', 'Maaf, gagal mengirim email. Coba lagi beberapa saat.');
        }

        return redirect()->to(site_url('kontak'))->with('success', 'Pesan Anda terkirim. Terima kasih!');
    }
}
