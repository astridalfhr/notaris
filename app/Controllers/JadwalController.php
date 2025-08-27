<?php

namespace App\Controllers;

use App\Models\JadwalModel;
use CodeIgniter\Controller;

class JadwalController extends Controller
{
    public function getJadwal($employeeId)
    {
        $model = new JadwalModel();
        $jadwal = $model->where('karyawan_id', $employeeId)->orderBy('tanggal')->findAll();

        return $this->response->setJSON($jadwal);
    }
}
