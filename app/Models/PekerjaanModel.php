<?php

namespace App\Models;

use CodeIgniter\Model;

class PekerjaanModel extends Model
{
    protected $table = 'pekerjaan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'category',     // 'PPAT' | 'NOTARIS'
        'title',
        'slug',
        'excerpt',
        'description',
        'icon',
        'url',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
    ];

    // timestamps
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Ambil list pekerjaan per kategori.
     *
     * @param string $cat        PPAT|NOTARIS (case-insensitive)
     * @param int    $limit      Batas jumlah; 0/negatif = tanpa batas
     * @param bool   $onlyActive Hanya yang aktif
     */
    public function listByCategory(string $cat, int $limit = 3, bool $onlyActive = true): array
    {
        $builder = $this->where('category', strtoupper($cat))
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC');

        if ($onlyActive) {
            $builder->where('is_active', 1);
        }

        if ($limit > 0) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Cek apakah slug sudah dipakai (opsional abaikan ID tertentu saat edit).
     */
    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $builder = $this->where('slug', $slug);
        if ($exceptId !== null) {
            $builder->where('id !=', $exceptId);
        }
        return $builder->countAllResults() > 0;
    }

    /**
     * Pencarian sederhana untuk halaman index admin.
     */
    public function search(string $q): array
    {
        $q = trim($q);
        $builder = $this->orderBy('id', 'DESC');

        if ($q !== '') {
            $builder->groupStart()
                ->like('title', $q)
                ->orLike('slug', $q)
                ->orLike('category', $q)
                ->orLike('icon', $q)
                ->orLike('excerpt', $q)
                ->groupEnd();
        }

        return $builder->findAll();
    }
}
