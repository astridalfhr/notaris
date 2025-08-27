<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmployeeModel;
use CodeIgniter\I18n\Time;

class Layanan extends BaseController
{
    public function index()
    {
        $employeeModel = new EmployeeModel();

        // Ambil semua pegawai untuk ditampilkan sebagai kartu
        $employees = $employeeModel->asArray()
            ->select('id, nama, jabatan, spesialisasi, foto')
            ->orderBy('nama', 'ASC')
            ->findAll();

        // Default tanggal tampilan (HARI INI, Asia/Jakarta), boleh dioverride via ?date=
        $tz = 'Asia/Jakarta';
        $date = (string) ($this->request->getGet('date') ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = Time::today($tz)->toDateString();
        }

        return view('layanan', [
            'employees' => $employees,
            'date' => $date, // dipakai untuk preset date picker di view
        ]);
    }
}
