<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteHomeModel extends Model
{
    protected $table         = 'site_home';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'hero_title', 'hero_tagline',
        'company_name', 'company_info',
        'news_title', 'news_body', 'news_link',
        'contact_whatsapp', 'contact_email',
    ];
    protected $useTimestamps = true; // needs created_at & updated_at columns
}
