<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $returnType = 'array';
    protected $table = 'booking';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'jadwal_id',
        'karyawan_id',
        'nama',
        'email',
        'no_telepon',
        'catatan',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
}
