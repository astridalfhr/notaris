<?php
namespace App\Controllers\Multiuser;

use App\Libraries\KerjaMenu;
use App\Controllers\BaseController;

class Kerja extends BaseController
{
    private array $MENU = [
        'ppat' => [
            ['slug' => 'ajb', 'title' => 'AJB (Akta Jual Beli)'],
            ['slug' => 'hibah', 'title' => 'Hibah'],
            ['slug' => 'turun-waris', 'title' => 'Turun Waris'],
            ['slug' => 'apht', 'title' => 'APHT'],
            ['slug' => 'ppjb', 'title' => 'PPJB'],
        ],
        'notaris' => [
            ['slug' => 'cv', 'title' => 'CV'],
            ['slug' => 'pt', 'title' => 'PT'],
            ['slug' => 'pergantian-pengurus', 'title' => 'Pergantian Pengurus'],
            ['slug' => 'pjb', 'title' => 'PJB'],
            ['slug' => 'skmht', 'title' => 'SKMHT'],
            ['slug' => 'waarmerking', 'title' => 'Waarmerking'],
            ['slug' => 'legalisasi', 'title' => 'Legalisasi'],
        ],
    ];

    private array $DESC = [
        'ppat' => [
            'ajb' => 'Pembuatan dan validasi Akta Jual Beli di hadapan PPAT.',
            'hibah' => 'Pengalihan hak melalui perjanjian hibah.',
            'turun-waris' => 'Pengurusan peralihan hak karena waris.',
            'apht' => 'Akta Pembebanan Hak Tanggungan untuk jaminan kredit.',
            'ppjb' => 'Perjanjian Pengikatan Jual Beli sebagai pendahuluan AJB.',
        ],
        'notaris' => [
            'cv' => 'Pendirian/perubahan Perseroan Komanditer.',
            'pt' => 'Pendirian/perubahan Perseroan Terbatas.',
            'pergantian-pengurus' => 'Pergantian direksi, komisaris, atau pengurus badan.',
            'pjb' => 'Perjanjian Jual Beli di hadapan notaris.',
            'skmht' => 'Surat Kuasa Membebankan Hak Tanggungan.',
            'waarmerking' => 'Waarmerking (pencatatan) dokumen.',
            'legalisasi' => 'Legalisasi tanda tangan pada dokumen.',
        ],
    ];

    public function index()
    {
        $data['menu'] = KerjaMenu::get();
        $userId = (int) (session('id') ?? session('user_id') ?? 0);
        $rootRel = "uploads/kerja/{$userId}";
        $rootAbs = FCPATH . $rootRel;

        $files = [];
        if (is_dir($rootAbs)) {
            $rii = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootAbs, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($rii as $fi) {
                if (!$fi->isFile())
                    continue;
                $abs = str_replace('\\', '/', $fi->getPathname());
                $rel = ltrim(str_replace(str_replace('\\', '/', FCPATH), '', $abs), '/');
                $url = base_url($rel);
                $kategori = '-';
                $sub = '-';
                if (preg_match('#^uploads/kerja/\d+/([^/]+)/([^/]+)/#i', $rel, $m)) {
                    $kategori = strtolower($m[1]);
                    $sub = strtolower($m[2]);
                }
                $files[] = [
                    'name' => $fi->getFilename(),
                    'url' => $url,
                    'rel' => $rel,
                    'mtime' => $fi->getMTime(),
                    'kategori' => $kategori,
                    'subkategori' => $sub,
                ];
            }
            usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        }

        date_default_timezone_set('Asia/Jakarta');

        return view('multiuser/kerja_index', [
            'menu' => $this->MENU,
            'files' => $files,
            'userId' => $userId,
        ]);
    }

    public function item(string $kategori, string $slug)
    {
        $kategori = strtolower($kategori);
        $slug = strtolower($slug);

        if (!isset($this->MENU[$kategori])) {
            return redirect()->to(site_url('multiuser/kerja'))->with('error', 'Kategori tidak dikenal.');
        }

        $title = null;
        foreach ($this->MENU[$kategori] as $it) {
            if ($it['slug'] === $slug) {
                $title = $it['title'];
                break;
            }
        }
        if (!$title) {
            return redirect()->to(site_url('multiuser/kerja'))->with('error', 'Item tidak ditemukan.');
        }

        $desc = $this->DESC[$kategori][$slug] ?? 'Deskripsi item belum ditambahkan.';
        $userId = (int) (session('id') ?? session('user_id') ?? 0);

        $dirRel = "uploads/kerja/{$userId}/{$kategori}/{$slug}";
        $dirAbs = FCPATH . $dirRel;

        $files = [];
        if (is_dir($dirAbs)) {
            foreach (scandir($dirAbs) as $name) {
                if ($name === '.' || $name === '..')
                    continue;
                $abs = $dirAbs . DIRECTORY_SEPARATOR . $name;
                if (!is_file($abs))
                    continue;
                $files[] = [
                    'name' => $name,
                    'url' => base_url($dirRel . '/' . rawurlencode($name)),
                    'rel' => $dirRel . '/' . $name,
                    'mtime' => filemtime($abs),
                ];
            }
            usort($files, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        }

        date_default_timezone_set('Asia/Jakarta');

        $data['menu'] = KerjaMenu::get();
        return view('multiuser/kerja_item', [
            'menu' => $this->MENU,
            'kategori' => $kategori,
            'slug' => $slug,
            'title' => $title,
            'desc' => $desc,
            'files' => $files,
            'userId' => $userId,
        ]);
    }

    public function upload()
    {
        $userId = (int) (session('id') ?? session('user_id') ?? 0);
        if ($userId <= 0)
            return redirect()->to('/login')->with('error', 'Sesi tidak valid.');

        $file = $this->request->getFile('berkas');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        $rules = ['berkas' => 'uploaded[berkas]|max_size[berkas,20480]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $origin = trim((string) $this->request->getPost('origin'), '/');
        if ($origin === '') {
            $k = trim((string) $this->request->getPost('kategori'), '/');
            $s = trim((string) $this->request->getPost('slug'), '/');
            $origin = ($k && $s) ? "{$k}/{$s}" : 'umum';
        }

        $destRel = "uploads/kerja/{$userId}/{$origin}";
        $destAbs = FCPATH . $destRel;
        if (!is_dir($destAbs))
            @mkdir($destAbs, 0775, true);

        $newName = $file->getRandomName();
        if (!$file->move($destAbs, $newName)) {
            return redirect()->back()->with('error', 'Gagal menyimpan file.');
        }

        $data['menu'] = KerjaMenu::get();
        return ($origin !== 'umum' && substr_count($origin, '/') === 1)
            ? redirect()->to(site_url("multiuser/kerja/{$origin}"))->with('success', 'Berkas diunggah.')
            : redirect()->back()->with('success', 'Berkas diunggah.');
    }

    public function delete()
    {
        $userId = (int) (session('id') ?? session('user_id') ?? 0);
        if ($userId <= 0)
            return redirect()->to('/login');

        $rel = str_replace('\\', '/', (string) $this->request->getPost('rel'));
        $rel = ltrim($rel, '/');

        $basePrefix = "uploads/kerja/{$userId}/";
        if ($rel === '' || strpos($rel, $basePrefix) !== 0) {
            return redirect()->back()->with('error', 'Target tidak valid.');
        }

        $abs = FCPATH . $rel;
        if (!is_file($abs)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        if (!@unlink($abs)) {
            return redirect()->back()->with('error', 'Gagal menghapus file.');
        }

        $data['menu'] = KerjaMenu::get();
        return redirect()->back()->with('success', 'File berhasil dihapus.');
    }

}
