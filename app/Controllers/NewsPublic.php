<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiteNewsModel;

class NewsPublic extends BaseController
{
    private int $perPage = 6;

    private function imageUrl(?string $fn): string
    {
        $fn = trim((string) $fn);
        if ($fn === '')
            return '';
        if (filter_var($fn, FILTER_VALIDATE_URL))
            return $fn; // sudah URL absolut
        return base_url('images/news/' . $fn);                // filename → folder images/news
    }

    public function feed()
    {
        $page = max(1, (int) $this->request->getGet('page'));
        $offset = ($page - 1) * $this->perPage;

        $model = new SiteNewsModel();

        // Hanya berita publish, urut dari terbaru
        $rows = $model->where('is_published', 1)
            ->orderBy('published_at', 'DESC')
            ->findAll($this->perPage, $offset);

        // Hitung total untuk has_more
        $total = (new SiteNewsModel())
            ->where('is_published', 1)
            ->countAllResults();

        // Normalisasi payload → pastikan gunakan body
        $items = array_map(function (array $r) {
            $published = $r['published_at'] ?? ($r['updated_at'] ?? $r['created_at'] ?? null);
            return [
                'id' => (int) ($r['id'] ?? 0),
                'title' => (string) ($r['title'] ?? ''),
                'slug' => (string) ($r['slug'] ?? ''),
                'excerpt' => (string) ($r['excerpt'] ?? ''), // opsional
                'body' => (string) ($r['body'] ?? ''),    // ← konten lengkap
                'image' => $this->imageUrl($r['image'] ?? ''),
                'is_featured' => (int) ($r['is_featured'] ?? 0),
                'published_at' => (string) $published,
            ];
        }, $rows);

        return $this->response->setJSON([
            'page' => $page,
            'per_page' => $this->perPage,
            'has_more' => ($offset + count($rows)) < $total,
            'items' => $items,
        ]);
    }

    public function show(int $id)
    {
        $row = (new SiteNewsModel())
            ->where('is_published', 1)
            ->find($id);

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $published = $row['published_at'] ?? ($row['updated_at'] ?? $row['created_at'] ?? null);

        $item = [
            'id' => (int) ($row['id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'slug' => (string) ($row['slug'] ?? ''),
            'body' => (string) ($row['body'] ?? ''),   // ← konten lengkap untuk modal
            'image' => $this->imageUrl($row['image'] ?? ''),
            'is_featured' => (int) ($row['is_featured'] ?? 0),
            'published_at' => (string) $published,
        ];

        return $this->response->setJSON($item);
    }
}
