<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\I18n\Time;
use Dompdf\Dompdf;
use Dompdf\Options;

class Arsip extends BaseController
{
    /** Direktori publik untuk menyimpan file arsip (pastikan writeable) */
    private string $storeDir = 'uploads/arsip';

    /** Cek apakah request mengharapkan JSON (kompatibel CI4 lawas) */
    private function acceptsJson(): bool
    {
        $accept = strtolower($this->request->getHeaderLine('Accept'));
        $xrw    = strtolower($this->request->getHeaderLine('X-Requested-With'));
        $isAjax = method_exists($this->request, 'isAJAX') ? $this->request->isAJAX() : ($xrw === 'xmlhttprequest');

        return $isAjax
            || strpos($accept, 'application/json') !== false
            || strpos($accept, 'text/json') !== false
            || strpos($accept, 'application/*+json') !== false;
    }

    /** Compat: cek method HTTP dengan fallback jika $request->is() tidak ada */
    private function isMethod(string $method): bool
    {
        $method = strtolower($method);
        if (method_exists($this->request, 'is')) {
            return $this->request->is($method);
        }
        return strtolower($this->request->getMethod()) === $method;
    }

    /** Hanya admin */
    private function ensureAdmin(): void
    {
        $role = strtolower((string) (session('role') ?? ''));
        if ($role !== 'admin') {
            if ($this->acceptsJson()) {
                $this->response->setStatusCode(403)
                    ->setJSON(['ok' => false, 'error' => 'Forbidden'])
                    ->send();
                exit;
            }
            redirect()->to('/login')->send();
            exit;
        }
    }

    /** Pastikan folder ada */
    private function ensureDir(): void
    {
        $abs = FCPATH . rtrim($this->storeDir, '/');
        if (!is_dir($abs)) {
            @mkdir($abs, 0775, true);
        }
    }

    /** Buat URL publik file */
    private function fileUrl(string $fileName): string
    {
        return base_url(rtrim($this->storeDir, '/') . '/' . ltrim($fileName, '/'));
    }

    /** Normalisasi bulan 'YYYY-MM' */
    private function getMonth(): string
    {
        $m = (string) ($this->request->getGet('month') ?? date('Y-m'));
        if (!preg_match('/^\d{4}\-\d{2}$/', $m)) {
            $m = date('Y-m');
        }
        return $m;
    }

    /** ====== FEED: ditampilkan ke SEMUA admin (tanpa filter pemilik) ====== */
    public function feed()
    {
        $this->ensureAdmin();

        $month = $this->getMonth();
        $db = db_connect();
        $rows = $db->query(
            'SELECT id, jenis, tanggal, nomor_surat, perihal, pihak, file_name, created_by, created_at
             FROM arsip_surat
             WHERE DATE_FORMAT(tanggal, "%Y-%m") = ?
             ORDER BY tanggal ASC, id ASC',
            [$month]
        )->getResultArray();

        $map = function (array $r) {
            $file = (string) ($r['file_name'] ?? '');
            return [
                'id'         => (int) ($r['id'] ?? 0),
                'jenis'      => (string) ($r['jenis'] ?? ''),
                'tanggal'    => (string) ($r['tanggal'] ?? ''),
                'nomor_surat'=> (string) ($r['nomor_surat'] ?? ''),
                'perihal'    => (string) ($r['perihal'] ?? ''),
                'pihak'      => (string) ($r['pihak'] ?? ''),
                'url'        => $file !== '' ? $this->fileUrl($file) : '',
                'created_by' => (int) ($r['created_by'] ?? 0),
                'created_at' => (string) ($r['created_at'] ?? ''),
            ];
        };

        $masuk = [];
        $keluar = [];
        foreach ($rows as $r) {
            if (strtolower((string) $r['jenis']) === 'masuk') {
                $masuk[] = $map($r);
            } else {
                $keluar[] = $map($r);
            }
        }

        return $this->response->setJSON([
            'ok'    => true,
            'month' => $month,
            'masuk' => $masuk,
            'keluar'=> $keluar
        ]);
    }

    /** ====== UPLOAD: semua admin boleh upload ====== */
    public function upload()
    {
        $this->ensureAdmin();
        if (!$this->isMethod('post')) {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'error' => 'Method Not Allowed']);
        }

        $jenis        = strtolower((string) $this->request->getPost('jenis'));
        $tanggal      = (string) $this->request->getPost('tanggal');
        $nomor_surat  = trim((string) $this->request->getPost('nomor_surat'));
        $perihal      = trim((string) $this->request->getPost('perihal'));
        $pihak        = trim((string) $this->request->getPost('pihak'));

        if (!in_array($jenis, ['masuk', 'keluar'], true)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Jenis harus "masuk" atau "keluar".']);
        }
        if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $tanggal)) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Tanggal tidak valid.']);
        }
        if ($perihal === '') {
            return $this->response->setJSON(['ok' => false, 'error' => 'Perihal wajib diisi.']);
        }

        // Auto-fill Pengirim (Surat Masuk)
        if ($jenis === 'masuk' && $pihak === '') {
            $pihak = (string) (session('nama') ?? 'Admin');
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['ok' => false, 'error' => 'File wajib diunggah.']);
        }
        if ($file->getSize() > 10 * 1024 * 1024) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Maksimum ukuran file 10MB.']);
        }

        $this->ensureDir();
        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-\_]/', '_', $file->getClientName());
        if (!$file->move(FCPATH . rtrim($this->storeDir, '/'), $safeName)) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'error' => 'Gagal menyimpan file.']);
        }

        $db = db_connect();
        $db->table('arsip_surat')->insert([
            'jenis'       => $jenis,
            'tanggal'     => $tanggal,
            'nomor_surat' => $nomor_surat,
            'perihal'     => $perihal,
            'pihak'       => $pihak,
            'file_name'   => $safeName,
            'created_by'  => (int) (session('id') ?? 0),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON(['ok' => true]);
    }

    /** ====== DELETE: semua admin boleh hapus ====== */
    public function delete(int $id)
    {
        $this->ensureAdmin();

        // Izinkan fallback POST (beberapa hosting blokir DELETE)
        if (!($this->isMethod('delete') || $this->isMethod('post'))) {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'error' => 'Method Not Allowed']);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setJSON(['ok' => false, 'error' => 'ID tidak valid']);
        }

        $db = db_connect();
        $row = $db->table('arsip_surat')->where('id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['ok' => false, 'error' => 'Data tidak ditemukan']);
        }

        // Hapus file fisik
        $file = (string) ($row['file_name'] ?? '');
        if ($file !== '') {
            $abs = FCPATH . rtrim($this->storeDir, '/') . '/' . $file;
            if (is_file($abs)) {
                @unlink($abs);
            }
        }

        $db->table('arsip_surat')->where('id', $id)->delete();

        return $this->response->setJSON(['ok' => true]);
    }

    /** ====== REPORT: Export PDF untuk satu bulan ====== */
    public function report()
    {
        $this->ensureAdmin();

        $month = $this->getMonth();
        $db = db_connect();
        $rows = $db->query(
            'SELECT * FROM arsip_surat WHERE DATE_FORMAT(tanggal, "%Y-%m")=? ORDER BY tanggal ASC, id ASC',
            [$month]
        )->getResultArray();

        $html = view('admin/arsip_report_pdf', ['rows' => $rows, 'month' => $month]);

        // Pastikan Dompdf tersedia. Railway akan install via composer saat build.
        if (!class_exists(Dompdf::class)) {
            // coba vendor autoload (jaga-jaga kalau autoload CI tidak mengikutkan vendor)
            $vendorAutoload = ROOTPATH . 'vendor/autoload.php';
            if (is_file($vendorAutoload)) {
                require_once $vendorAutoload;
            }

            // fallback manual jika kamu taruh dompdf di app/ThirdParty/dompdf
            if (!class_exists(Dompdf::class)) {
                $thirdPartyAutoload = APPPATH . 'ThirdParty/dompdf/autoload.inc.php';
                if (is_file($thirdPartyAutoload)) {
                    require_once $thirdPartyAutoload;
                }
            }
        }

        if (!class_exists(Dompdf::class)) {
            return $this->response->setStatusCode(500)->setBody(
                'Dompdf belum terpasang. Jalankan "composer require dompdf/dompdf" ' .
                'atau taruh dompdf di app/ThirdParty/dompdf (dengan autoload.inc.php).'
            );
        }

        // Options opsional: hanya set jika klasnya ada
        $opts = null;
        if (class_exists(Options::class)) {
            $opts = new Options();
            $opts->set('isRemoteEnabled', true);
            $opts->set('isHtml5ParserEnabled', true);
        }

        $dompdf = $opts ? new Dompdf($opts) : new Dompdf();
        if (!$opts) {
            // jika Options tidak ada (versi dompdf lebih lawas), set via set_option
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_option('isHtml5ParserEnabled', true);
        }

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="Laporan-Surat-' . $month . '.pdf"')
            ->setBody($dompdf->output());
    }

    /** ====== UPDATE: edit metadata + (opsional) ganti file ====== */
    public function update($id)
    {
        $this->ensureAdmin();
        if (!$this->isMethod('post')) {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'error' => 'Method Not Allowed']);
        }

        $id = (int) $id;
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'error' => 'ID tidak valid']);
        }

        $db = db_connect();
        $row = $db->table('arsip_surat')->where('id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'Data tidak ditemukan']);
        }

        $jenis   = strtolower((string) ($this->request->getPost('jenis') ?: $row['jenis']));
        if (!in_array($jenis, ['masuk', 'keluar'], true)) {
            $jenis = (string) $row['jenis'];
        }

        $tanggal = (string) ($this->request->getPost('tanggal') ?: $row['tanggal']);
        if (!preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $tanggal)) {
            $tanggal = (string) $row['tanggal'];
        }

        $perihal = (string) ($this->request->getPost('perihal') ?: $row['perihal']);
        $pihak   = (string) ($this->request->getPost('pihak') ?: $row['pihak']);
        $nomor   = (string) ($this->request->getPost('nomor_surat')
            ?: $this->request->getPost('no_surat')
            ?: $this->request->getPost('nomor')
            ?: $row['nomor_surat']);

        $data = [
            'jenis'       => $jenis,
            'tanggal'     => $tanggal,
            'nomor_surat' => trim($nomor),
            'perihal'     => trim($perihal),
            'pihak'       => trim($pihak),
            'updated_at'  => date('Y-m-d H:i:s'),
            'updated_by'  => (int) (session('id') ?? 0), // kolom opsional
        ];

        // Ganti file (opsional)
        $file = $this->request->getFile('file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($file->getSize() > 10 * 1024 * 1024) {
                return $this->response->setStatusCode(413)->setJSON(['ok' => false, 'error' => 'Maks 10MB.']);
            }
            $this->ensureDir();
            $newName = time() . '_' . preg_replace('/[^a-zA-Z0-9\.\-\_]/', '_', $file->getClientName());
            if (!$file->move(FCPATH . rtrim($this->storeDir, '/'), $newName)) {
                return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'error' => 'Gagal menyimpan file baru.']);
            }
            // hapus file lama
            $old = (string) ($row['file_name'] ?? '');
            if ($old) {
                $abs = FCPATH . rtrim($this->storeDir, '/') . '/' . $old;
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }
            $data['file_name'] = $newName;
        }

        $db->table('arsip_surat')->where('id', $id)->update($data);

        return $this->response->setJSON(['ok' => true]);
    }
}
