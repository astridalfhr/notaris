<?php
namespace App\Models;

use CodeIgniter\Model;

class SiteSettingsModel extends Model
{
    protected $table = 'site_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'context',
        'company_name',
        'company_info',
        'owner_name',
        'owner_subtitle',
        'owner_photo',
        'address',
        'map_embed',
        'social_email',
        'social_instagram',
        'social_whatsapp',
        'social_linkedin',
        'hero_title',
        'hero_tagline', 
        'is_active',
    ];
    protected $useTimestamps = true; 
}
