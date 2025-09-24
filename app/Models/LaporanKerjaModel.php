<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanKerjaModel extends Model
{
    protected $table = 'laporan_kerja';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'kategori',
        'nomor_global',
        'nomor_bulanan',
        'tahun',
        'bulan',
        'tanggal',
        'data_json',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false;

    public function getMonthly(string $kategori, int $bulan, int $tahun): array
    {
        // NB: tahun/bulan di DB kamu char — int tetap OK (MySQL cast)
        return $this->where('kategori', strtoupper($kategori))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('nomor_bulanan', 'ASC')
            ->findAll();
    }

    public function getMonthlyWithFiles(string $kategori, int $bulan, int $tahun): array
    {
        $rows = $this->getMonthly($kategori, $bulan, $tahun);
        if (!$rows)
            return [];

        $ids = array_column($rows, 'id');
        $lamp = model(\App\Models\LaporanLampiranModel::class)->listByLaporanIds($ids);

        foreach ($rows as &$r) {
            $r['files'] = $lamp[$r['id']] ?? [];
        }
        return $rows;
    }

    // ====== createNotaris/createPPAT tetap seperti kamu kirim (sudah OK) =====
    public function nextNomorBulanan(string $kategori, int $bulan, int $tahun): int
    {
        $row = $this->select('MAX(nomor_bulanan) AS mx')
            ->where('kategori', strtoupper($kategori))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        $mx = (int) ($row['mx'] ?? 0);
        return $mx + 1;
    }

    public function createNotaris(array $payload, int $createdBy, ?string $tanggal = null): int
    {
        $bulan = (int) ($payload['bulan'] ?? date('n'));
        $tahun = (int) ($payload['tahun'] ?? date('Y'));

        $data = [
            'kategori' => 'NOTARIS',
            'nomor_global' => $payload['nomor_global'] ?? null, // <-- sekarang boleh NULL di DB
            'nomor_bulanan' => (int) ($payload['nomor_bulanan'] ?? $this->nextNomorBulanan('NOTARIS', $bulan, $tahun)),
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggal' => $tanggal ?: ($payload['tanggal'] ?? date('Y-m-d')),
            'data_json' => json_encode([
                'tanggal' => $payload['tanggal'] ?? null,
                'sifat' => $payload['sifat'] ?? null,
                'nama_penghadap' => $payload['nama_penghadap'] ?? null,
                'kuasa' => $payload['kuasa'] ?? null,
            ], JSON_UNESCAPED_UNICODE),
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->insert($data, true);
        return (int) $this->getInsertID();
    }

    public function createPPAT(array $payload, int $createdBy): int
    {
        $bulan = (int) ($payload['bulan'] ?? date('n'));
        $tahun = (int) ($payload['tahun'] ?? date('Y'));

        $dataJson = [
            'akta_no' => $payload['akta_no'] ?? null,
            'akta_tgl' => $payload['akta_tgl'] ?? null,
            'bentuk' => $payload['bentuk'] ?? null,
            'pihak_pengalih' => $payload['pihak_pengalih'] ?? null,
            'pihak_penerima' => $payload['pihak_penerima'] ?? null,
            'jenis_hak' => $payload['jenis_hak'] ?? null,
            'nomor_hak' => $payload['nomor_hak'] ?? null,
            'letak' => $payload['letak'] ?? null,
            'luas_tnh' => $payload['luas_tnh'] ?? null,
            'luas_bgn' => $payload['luas_bgn'] ?? null,
            'nilai_transaksi' => $payload['nilai_transaksi'] ?? null,
            'sspt_nop' => $payload['sspt_nop'] ?? null,
            'sspt_tahun' => $payload['sspt_tahun'] ?? null,
            'njop' => $payload['njop'] ?? null,
            'sep_nilai' => $payload['sep_nilai'] ?? null,
            'sep_tgl' => $payload['sep_tgl'] ?? null,
            'bphtb_nilai' => $payload['bphtb_nilai'] ?? null,
            'ket' => $payload['ket'] ?? null,
        ];

        $data = [
            'kategori' => 'PPAT',
            'nomor_global' => $payload['nomor_global'] ?? null, // <-- sekarang boleh NULL di DB
            'nomor_bulanan' => (int) ($payload['nomor_bulanan'] ?? $this->nextNomorBulanan('PPAT', $bulan, $tahun)),
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggal' => $payload['akta_tgl'] ?? date('Y-m-d'),
            'data_json' => json_encode($dataJson, JSON_UNESCAPED_UNICODE),
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->insert($data, true);
        return (int) $this->getInsertID();
    }
}
