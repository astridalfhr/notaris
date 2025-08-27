<?php
namespace App\Models;

use CodeIgniter\Model;

class SiteHeroModel extends Model
{
    protected $table = 'site_hero';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title',
        'tagline',
        'image',
        'button_text',
        'button_link',
        'sort_order',
        'is_active'
    ];
    protected $useTimestamps = true;
    protected $returnType = 'array';
}
