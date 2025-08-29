<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;
use App\Models\SiteNewsModel;
use App\Models\SiteHeroModel;

class Home extends BaseController
{
    // jumlah berita per halaman (load more)
    private int $newsPerPage = 6;

    /** Bangun URL gambar absolut dari nama file/URL */
    private function imgUrl(?string $fn): string
    {
        $fn = trim((string) $fn);
        if ($fn === '')
            return '';
        // kalau sudah URL penuh, kembalikan apa adanya
        if (filter_var($fn, FILTER_VALIDATE_URL))
            return $fn;
        // simpan file di public/images/news/
        return base_url('images/news/' . $fn);
    }

    /** Halaman beranda (hero + berita + section lainnya) */
    public function index()
    {
        // Settings aktif untuk home
        $settings = (new SiteSettingsModel())
            ->where('context', 'home')
            ->where('is_active', 1)
            ->orderBy('updated_at', 'DESC')
            ->first() ?? [];

        // Hero slides aktif (utama). Jika kosong, slider fallback pakai featured news
        $heroes = (new SiteHeroModel())
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll(10);

        $news = new SiteNewsModel();

        // 6 berita awal untuk SSR fallback (supaya halaman tetap ada konten tanpa JS)
        $latest = $news->select('id,title,excerpt,body,image,published_at')
            ->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll($this->newsPerPage);

        // max 3 news featured untuk fallback slider jika heroes kosong
        $featured = $news->select('id,title,excerpt,body,image,published_at')
            ->where('is_published', 1)
            ->where('is_featured', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll(3);

        return view('home', compact('settings', 'heroes', 'latest', 'featured'));
    }

    /** Endpoint JSON: feed berita (paginated) untuk “Berita lainnya” */
    public function newsFeed()
    {
        $page = max(1, (int) $this->request->getGet('page'));
        $offset = ($page - 1) * $this->newsPerPage;

        $m = new SiteNewsModel();

        $rows = $m->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll($this->newsPerPage, $offset);

        $total = (new SiteNewsModel())
            ->where('is_published', 1)
            ->countAllResults();

        $items = array_map(function (array $r) {
            $published = $r['published_at'] ?? ($r['updated_at'] ?? $r['created_at'] ?? null);
            return [
                'id' => (int) ($r['id'] ?? 0),
                'title' => (string) ($r['title'] ?? ''),
                'excerpt' => (string) ($r['excerpt'] ?? ''),
                'body' => (string) ($r['body'] ?? ''),         // HTML utuh (dipakai modal)
                'image' => $this->imgUrl($r['image'] ?? ''),   // selalu URL penuh
                'published_at' => (string) $published,
            ];
        }, $rows);

        return $this->response->setJSON([
            'page' => $page,
            'per_page' => $this->newsPerPage,
            'has_more' => ($offset + count($rows)) < $total,
            'items' => $items,
        ]);
    }

    /** Endpoint JSON: detail berita by id (publik hanya yang publish) */
    public function newsShow($id = null)
    {
        if (!$id || !ctype_digit((string) $id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Bad id']);
        }
        $id = (int) $id;

        $m = new SiteNewsModel();
        // penting: gunakan where()->first() (jangan find()+where)
        $row = $m->where('id', $id)
            ->where('is_published', 1) // publik hanya yg publish
            ->first();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $published = $row['published_at'] ?? ($row['updated_at'] ?? $row['created_at'] ?? null);

        return $this->response
            ->setContentType('application/json')
            ->setJSON([
                'id' => (int) $row['id'],
                'title' => (string) $row['title'],
                'body' => (string) ($row['body'] ?? ''),
                'image' => $this->imgUrl($row['image'] ?? ''), // selalu URL penuh
                'published_at' => (string) $published,
            ]);
    }

    /** Halaman kontak (tetap) */
    public function kontak()
    {
        return view('kontak');
    }
}
