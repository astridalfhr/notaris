<?php

namespace App\Controllers;

use App\Models\JadwalModel;
use CodeIgniter\Controller;

class JadwalController extends Controller
{
    public function getJadwal($employeeId)
    {
        $employeeId = (int) $employeeId;
        $date = (string) ($this->request->getGet('date') ?? '');

        $m = new JadwalModel();
        $qb = $m->where('karyawan_id', $employeeId)
            ->orderBy('tanggal', 'ASC')
            ->orderBy('jam', 'ASC');

        // dukung date=YYYY-MM-DD baik kolom DATE maupun DATETIME
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $qb->groupStart()
                ->where('tanggal', $date)          // jika kolom DATE
                ->orWhere('DATE(tanggal)', $date)  // jika kolom DATETIME
                ->groupEnd();
        }

        $rows = $qb->findAll();

        // normalisasi output agar konsisten di front-end
        $out = array_map(function (array $r) {
            $tglRaw = (string) ($r['tanggal'] ?? '');
            $status = $r['status'] ?? '';
            $r['tanggal'] = substr($tglRaw, 0, 10); // "YYYY-MM-DD"
            // status bisa "available"/"booked" atau 1/0
            if (is_numeric($status)) {
                $r['status'] = ((int) $status === 1) ? 'available' : 'booked';
            } else {
                $r['status'] = strtolower(trim((string) $status)) ?: 'available';
            }
            return $r;
        }, $rows);

        return $this->response->setJSON($out);
    }
}
