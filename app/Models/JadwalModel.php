<?php namespace App\Models;

use CodeIgniter\Model;

class JadwalModel extends Model
{
    protected $table      = 'konsultasi_jadwal';
    protected $primaryKey = 'id';

    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'karyawan_id', 'tanggal', 'jam', 'status'
    ];
}
