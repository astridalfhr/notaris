<?php namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table      = 'employees';
    protected $primaryKey = 'id';

    protected $returnType    = 'array';
    protected $useTimestamps = true; 

    protected $allowedFields = [
        'nama', 'email', 'no_telepon',
        'deskripsi',
        'jabatan', 'spesialisasi', 'foto', 'status',
        'created_at',
    ];
}
