<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;
use App\Models\SiteNewsModel;
use App\Models\SiteHeroModel;

class Home extends BaseController
{
    private int $newsPerPage = 6;

    private function imgUrl(?string $fn): string
    {
        $fn = trim((string) $fn);
        if ($fn === '')
            return '';
        if (filter_var($fn, FILTER_VALIDATE_URL))
            return $fn;
        return base_url('images/news/' . $fn);
    }

    public function index()
    {
        $settings = (new SiteSettingsModel())
            ->where('context', 'home')
            ->where('is_active', 1)
            ->orderBy('updated_at', 'DESC')
            ->first() ?? [];

        $heroes = (new SiteHeroModel())
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll(10);

        $news = new SiteNewsModel();

        $latest = $news->select('id,title,excerpt,body,image,published_at')
            ->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll($this->newsPerPage);

        $featured = $news->select('id,title,excerpt,body,image,published_at')
            ->where('is_published', 1)
            ->where('is_featured', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll(3);

        return view('home', compact('settings', 'heroes', 'latest', 'featured'));
    }

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
                'body' => (string) ($r['body'] ?? ''),
                'image' => $this->imgUrl($r['image'] ?? ''),
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

    public function newsShow($id = null)
    {
        if (!$id || !ctype_digit((string) $id)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Bad id']);
        }
        $id = (int) $id;

        $m = new SiteNewsModel();
        $row = $m->where('id', $id)->where('is_published', 1)->first();
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $published = $row['published_at'] ?? ($row['updated_at'] ?? $row['created_at'] ?? null);

        return $this->response->setContentType('application/json')->setJSON([
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'body' => (string) ($row['body'] ?? ''),
            'image' => $this->imgUrl($row['image'] ?? ''),
            'published_at' => (string) $published,
        ]);
    }

    public function kontak()
    {
        return view('kontak');
    }
}
