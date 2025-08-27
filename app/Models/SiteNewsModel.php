<?php
namespace App\Models;
use CodeIgniter\Model;

class SiteNewsModel extends Model
{
    protected $table = 'site_news';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title',
        'slug',
        'excerpt',
        'body',
        'image',
        'is_featured',
        'is_published',
        'published_at'
    ];
    protected $useTimestamps = true;
    protected $returnType = 'array';
}
