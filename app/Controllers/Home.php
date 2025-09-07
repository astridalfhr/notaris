<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;
use App\Models\SiteNewsModel;

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
        // Deskripsi singkat (opsional)
        $desc = [
            'AKTA JUAL BELI' => 'Pembuatan akta jual beli properti.',
            'PENDIRIAN PT' => 'Pendirian Perseroan Terbatas beserta dokumen legal.',
            'AKTA HIBAH' => 'Akta hibah untuk pengalihan hak milik.',
            'SKMHT' => 'Surat Kuasa Membebankan Hak Tanggungan.',
            'ROYA' => 'Penghapusan Hak Tanggungan (roya).',
            'PENGECEKAN SERTIPIKAT' => 'Cek keabsahan/riwayat sertipikat.',
            'LAPORAN BULANAN' => 'Rekap aktivitas bulanan.',
        ];

        $ppatList = [
            'CEK LOKASI',
            'CEK KAWASAN',
            'VALIDASI',
            'ALIH WILAYAH',
            'PEMULIHAN DATA',
            'ROYA',
            'PENGECEKAN SERTIPIKAT',
            'AKTA JUAL BELI',
            'AKTA HIBAH',
            'AKTA PEMBAGIAN HAK BERSAMA',
            'TURUN WARIS',
            'PEMISAHAN',
            'PENINGKATAN HAK',
            'PELEPASAN HAK',
            'TURUN HAK',
            'UBAH LAHAN PERTANIAN JADI LAHAN PEKARANGAN',
            'LAPORAN BULANAN'
        ];
        $notarisList = [
            'SKMHT',
            'PENDIRIAN PT',
            'PERUBAHAN PT',
            'PENDIRIAN YAYASAN',
            'PERUBAHAN YAYASAN',
            'PENDIRIAN PERKUMPULAN',
            'PERUBAHAN PERKUMPULAN',
            'PENDIRIAN PERSEROAN KOMANDITER',
            'PERUBAHAN PERSEROAN KOMANDITER',
            'PENDIRIAN KOPERASI',
            'PERUBAHAN KOPERASI',
            'PERJANJIAN JUAL BELI',
            'KUASA UNTUK MENJUAL',
            'PERJANJIAN - PERJANJIAN'
        ];

        // === Font Awesome Free icon classes ===
        $iconMap = [
            // PPAT
            'CEK LOKASI' => 'fa-location-dot',
            'CEK KAWASAN' => 'fa-map',
            'VALIDASI' => 'fa-circle-check',
            'ALIH WILAYAH' => 'fa-arrows-left-right',
            'PEMULIHAN DATA' => 'fa-database',
            'ROYA' => 'fa-shield-halved',
            'PENGECEKAN SERTIPIKAT' => 'fa-magnifying-glass',
            'AKTA JUAL BELI' => 'fa-file-pen',
            'AKTA HIBAH' => 'fa-gift',
            'AKTA PEMBAGIAN HAK BERSAMA' => 'fa-layer-group',
            'TURUN WARIS' => 'fa-user-group',
            'PEMISAHAN' => 'fa-scissors',
            'PENINGKATAN HAK' => 'fa-arrow-up',
            'PELEPASAN HAK' => 'fa-handshake-angle',
            'TURUN HAK' => 'fa-arrow-down',
            'UBAH LAHAN PERTANIAN JADI LAHAN PEKARANGAN' => 'fa-seedling',
            'LAPORAN BULANAN' => 'fa-calendar-days',

            // Notaris
            'SKMHT' => 'fa-stamp',
            'PENDIRIAN PT' => 'fa-building',
            'PERUBAHAN PT' => 'fa-pen-to-square',
            'PENDIRIAN YAYASAN' => 'fa-hand-holding-heart', 
            'PERUBAHAN YAYASAN' => 'fa-pen-to-square',
            'PENDIRIAN PERKUMPULAN' => 'fa-users',
            'PERUBAHAN PERKUMPULAN' => 'fa-pen-to-square',
            'PENDIRIAN PERSEROAN KOMANDITER' => 'fa-briefcase',
            'PERUBAHAN PERSEROAN KOMANDITER' => 'fa-pen-to-square',
            'PENDIRIAN KOPERASI' => 'fa-store',
            'PERUBAHAN KOPERASI' => 'fa-pen-to-square',
            'PERJANJIAN JUAL BELI' => 'fa-handshake',
            'KUASA UNTUK MENJUAL' => 'fa-file-pen',
            'PERJANJIAN – PERJANJIAN' => 'fa-file-contract',
        ];

        $build = function (array $names, string $cat) use ($desc, $iconMap) {
            $out = [];
            foreach ($names as $n) {
                $slug = $this->slugify($n);
                $out[] = [
                    'name' => $n,
                    'slug' => $slug,
                    'category' => $cat,
                    'desc' => $desc[$n] ?? 'Klik untuk melihat ketersediaan jadwal.',
                    'icon' => $iconMap[$n] ?? 'fa-file-lines', // fallback aman
                ];
            }
            return $out;
        };

        return [
            'ppat' => $build($ppatList, 'PPAT'),
            'notaris' => $build($notarisList, 'Notaris'),
        ];
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
