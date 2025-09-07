<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\EmployeeResolver;
use App\Models\BookingModel;
use App\Models\JadwalModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Booking extends BaseController
{
    private function wantsJson(): bool
    {
        $xh = strtolower($this->request->getHeaderLine('X-Requested-With'));
        if ($xh === 'xmlhttprequest')
            return true;
        return stripos($this->request->getHeaderLine('Accept'), 'application/json') !== false;
    }

    private function json($ok, $message = '', $http = 200, array $extra = [])
    {
        if ($this->wantsJson()) {
            return $this->response->setStatusCode($http)->setJSON(array_merge(['ok' => $ok, 'message' => $message], $extra));
        }
        // fallback non-AJAX
        $type = $ok ? 'success' : ($http >= 400 ? 'error' : 'warning');
        return redirect()->back()->with($type, $message);
    }

    // helper: cek kepemilikan booking (b.karyawan_id atau kj.karyawan_id)
    private function findOwnedBooking(int $bookingId, int $employeeId): ?array
    {
        $db = db_connect();
        return $db->table('booking b')
            ->select('b.id, b.status, b.jadwal_id, b.karyawan_id, kj.karyawan_id AS kj_emp')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->where('b.id', $bookingId)
            ->get()->getRowArray();
    }

    public function confirm($id)
    {
        if (!session('id'))
            return $this->json(false, 'Unauthenticated', 401);
        if ($this->request->getMethod() !== 'post')
            return $this->json(false, 'Method not allowed', 405);

        $id = (int) $id;
        $employeeId = (int) EmployeeResolver::ensureForCurrentUser();

        $row = $this->findOwnedBooking($id, $employeeId);
        if (!$row)
            return $this->json(false, 'Booking tidak ditemukan.', 404);
        if ((int) $row['karyawan_id'] !== $employeeId && (int) ($row['kj_emp'] ?? 0) !== $employeeId) {
            return $this->json(false, 'Tidak berhak mengonfirmasi booking ini.', 403);
        }

        $m = new BookingModel();
        if (!$m->update($id, ['status' => 'confirmed'])) {
            return $this->json(false, 'Gagal mengonfirmasi booking.', 500);
        }
        return $this->json(true, 'Booking dikonfirmasi.');
    }

    public function cancel($id)
    {
        if (!session('id'))
            return $this->json(false, 'Unauthenticated', 401);
        if ($this->request->getMethod() !== 'post')
            return $this->json(false, 'Method not allowed', 405);

        $id = (int) $id;
        $employeeId = (int) EmployeeResolver::ensureForCurrentUser();

        $row = $this->findOwnedBooking($id, $employeeId);
        if (!$row)
            return $this->json(false, 'Booking tidak ditemukan.', 404);
        if ((int) $row['karyawan_id'] !== $employeeId && (int) ($row['kj_emp'] ?? 0) !== $employeeId) {
            return $this->json(false, 'Tidak berhak membatalkan booking ini.', 403);
        }

        $db = db_connect();
        $db->transStart();
        try {
            (new BookingModel())->update($id, ['status' => 'canceled']);
            if (!empty($row['jadwal_id'])) {
                (new JadwalModel())->update((int) $row['jadwal_id'], ['status' => 'available']);
            }
        } catch (DatabaseException $e) {
            $db->transRollback();
            return $this->json(false, 'Gagal membatalkan booking.', 500);
        }
        $db->transComplete();

        return $this->json(true, 'Booking dibatalkan & slot dikembalikan.');
    }

    // Versi “Selesai” berdasarkan BOOKING ID (biar cocok dgn JS)
    public function complete($id)
    {
        if (!session('id'))
            return $this->json(false, 'Unauthenticated', 401);
        if ($this->request->getMethod() !== 'post')
            return $this->json(false, 'Method not allowed', 405);

        $id = (int) $id;
        $employeeId = (int) EmployeeResolver::ensureForCurrentUser();

        $row = $this->findOwnedBooking($id, $employeeId);
        if (!$row)
            return $this->json(false, 'Booking tidak ditemukan.', 404);
        if ((int) $row['karyawan_id'] !== $employeeId && (int) ($row['kj_emp'] ?? 0) !== $employeeId) {
            return $this->json(false, 'Tidak berhak menyelesaikan booking ini.', 403);
        }

        $ok = (new BookingModel())->update($id, ['status' => 'completed', 'updated_at' => date('Y-m-d H:i:s')]);
        if (!$ok)
            return $this->json(false, 'Gagal menandai sebagai selesai.', 500);

        return $this->json(true, 'Booking ditandai selesai.');
    }

    // Opsional: feed “hari ini” yang dipanggil view kamu ⇒ /admin/booking/today
    public function today()
    {
        if (!session('id'))
            return $this->json(false, 'Unauthenticated', 401);

        $employeeId = (int) EmployeeResolver::ensureForCurrentUser();
        $tz = 'Asia/Jakarta';
        $today = \CodeIgniter\I18n\Time::today($tz);
        $dateStr = $today->toDateString();
        $start = $today->toDateTimeString();
        $end = \CodeIgniter\I18n\Time::tomorrow($tz)->toDateTimeString();

        $db = db_connect();
        $rows = $db->table('booking b')
            ->select('
                b.id AS booking_id, b.status, b.created_at,
                u.nama AS user_nama, u.email AS user_email,
                kj.id AS jadwal_id, kj.tanggal, kj.jam
            ')
            ->join('users u', 'u.id = b.user_id', 'left')
            ->join('konsultasi_jadwal kj', 'kj.id = b.jadwal_id', 'left')
            ->groupStart()
            ->where('kj.karyawan_id', $employeeId)
            ->orWhere('b.karyawan_id', $employeeId)
            ->groupEnd()
            ->groupStart()
            ->where('DATE(kj.tanggal)', $dateStr)
            ->orGroupStart()
            ->where('kj.tanggal >=', $start)
            ->where('kj.tanggal <', $end)
            ->groupEnd()
            ->groupEnd()
            ->orderBy('kj.jam', 'ASC')->orderBy('b.created_at', 'ASC')
            ->get()->getResultArray();

        // JS kamu pakai key "rows"
        return $this->response->setJSON(['ok' => true, 'rows' => $rows]);
    }
}
