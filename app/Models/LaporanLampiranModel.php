<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanLampiranModel extends Model
{
    protected $table = 'laporan_lampiran';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // Kolom sesuai schema dump + controller::insert()
    protected $allowedFields = [
        'laporan_id',
        'kategori',
        'file_name',
        'original',
        'mime',
        'size',
        'path',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Ambil lampiran per banyak laporan (group by laporan_id)
     */
    public function listByLaporanIds(array $ids): array
    {
        if (empty($ids))
            return [];

        $rows = $this->whereIn('laporan_id', $ids)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        $out = [];
        foreach ($rows as $r) {
            $out[$r['laporan_id']][] = [
                'id' => (int) $r['id'],
                'kategori' => $r['kategori'],
                'file_name' => $r['file_name'],
                'original' => $r['original'],
                'mime' => $r['mime'],
                'size' => $r['size'],
                'path' => $r['path'],
                'created_at' => $r['created_at'],
            ];
        }
        return $out;
    }

    /**
     * Insert banyak nama file sekaligus (optional helper)
     */
    public function addMany(int $laporanId, array $files): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($files as $f) {
            $this->insert([
                'laporan_id' => $laporanId,
                'file_name' => $f,
                'created_at' => $now,
            ], true);
        }
    }
}
