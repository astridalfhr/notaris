<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;
use App\Models\SiteNewsModel;
use App\Models\SiteHeroModel;

class Home extends BaseController
{

    public function index()
    {
        $settings = (new \App\Models\SiteSettingsModel())
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
        $latest = $news->where('is_published', 1)->orderBy('published_at', 'DESC')->findAll(6);
        $featured = $news->where('is_published', 1)->where('is_featured', 1)->orderBy('published_at', 'DESC')->findAll(3);

        return view('home', compact('settings', 'heroes', 'latest', 'featured'));
    }

    public function kontak()
    {
        return view('kontak');
    }
}
