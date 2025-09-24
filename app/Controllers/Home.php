<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;
use App\Models\SiteNewsModel;

use App\Models\PekerjaanModel;


class Home extends BaseController
{
    private int $newsPerPage = 6;

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = str_replace(["–", "—"], "-", $s);
        $s = preg_replace('/[^a-z0-9\s\-]/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('/\-+/', '-', $s);
        return trim($s, '-');
    }

    private function imgUrl(?string $fn): string
    {
        $fn = trim((string) $fn);
        if ($fn === '')
            return '';
        if (filter_var($fn, FILTER_VALIDATE_URL))
            return $fn;
        return base_url('images/news/' . $fn);
    }

    /**
     * Katalog layanan dikelompokkan + Font Awesome icon (free)
     * @return array{ppat: array<int,array>, notaris: array<int,array>}
     */

    private function servicesByCategory(): array
    {
        $M = new PekerjaanModel();

        // cek ada data aktif?
        $has = $M->where('is_active', 1)->countAllResults(false) > 0;
        if ($has) {
            $map = function (array $rows, string $catLabel) {
                $out = [];
                foreach ($rows as $r) {
                    $out[] = [
                        'name' => $r['title'],
                        'slug' => $r['slug'],
                        'category' => $catLabel,
                        'desc' => $r['excerpt'] ?: 'Klik untuk melihat ketersediaan jadwal.',
                        'icon' => $r['icon'] ?: 'fa-solid fa-file-lines',
                        'url' => $r['url'] ?? null,
                    ];
                }
                return $out;
            };

            $ppatRows = $M->where('category', 'PPAT')
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll(); // semua; kalau mau batasi top-N -> findAll($limit)

            // reset builder sebelum query lain
            $M->resetQuery();

            $notRows = $M->where('category', 'NOTARIS')
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            return [
                'ppat' => $map($ppatRows, 'PPAT'),
                'notaris' => $map($notRows, 'Notaris'),
            ];
        }
    }
    public function index()
    {
        $settings = (new SiteSettingsModel())
            ->where('context', 'home')->where('is_active', 1)
            ->orderBy('updated_at', 'DESC')->first() ?? [];

        $svc = $this->servicesByCategory();

        // kompat
        $heroes = [];
        $latest = [];
        $featured = [];

        return view('home', [
            'settings' => $settings,
            'servicesPPAT' => $svc['ppat'],
            'servicesNotaris' => $svc['notaris'],
            'heroes' => $heroes,
            'latest' => $latest,
            'featured' => $featured,
        ]);
    }
}
