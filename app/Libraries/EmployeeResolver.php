<?php
namespace App\Libraries;

use App\Models\EmployeeModel;

/**
 * EmployeeResolver memastikan ada baris pada tabel `employees` untuk
 * user yang sedang login, lalu mengembalikan employees.id.
 *
 * Prinsip penting:
 * - "Sumber kebenaran" untuk nama karyawan adalah `employees.nama`.
 * - JANGAN timpa `employees.nama` dari session jika `employees.nama` sudah terisi.
 * - Boleh mengisi `employees.nama` pakai session hanya ketika kosong.
 * - Session `nama` selalu diselaraskan ke nama di employees agar UI konsisten.
 */
class EmployeeResolver
{
    /**
     * Pastikan ada baris employees utk user login (by user_id kalau ada, atau email),
     * lalu kembalikan employees.id (AUTO_INCREMENT).
     *
     * Wajib: session('email') terisi. Kalau kosong → lempar exception.
     */
    public static function ensureForCurrentUser(): int
    {
        $email = trim((string) (session('email') ?? ''));
        $namaSes = trim((string) (session('nama') ?? ''));
        $userId = (int) (session('id') ?? 0);

        if ($email === '') {
            throw new \RuntimeException('Email akun kosong. Setel email akun admin terlebih dulu.');
        }

        $m = new EmployeeModel();
        $db = db_connect();

        // Deteksi apakah tabel employees punya kolom user_id
        $empFields = array_map(static fn($f) => $f->name, $db->getFieldData('employees'));
        $hasUserId = in_array('user_id', $empFields, true);

        // 1) Cari berdasarkan user_id jika kolom ada & session punya id
        $emp = null;
        if ($hasUserId && $userId > 0) {
            $emp = $m->where('user_id', $userId)->first();
        }

        // 2) Kalau belum ketemu, cari berdasarkan email (unik)
        if (!$emp) {
            $emp = $m->where('email', $email)->first();
        }

        if ($emp) {
            // HANYA isi nama dari session jika nama di employees masih kosong
            if (empty($emp['nama']) && $namaSes !== '') {
                $m->update($emp['id'], ['nama' => $namaSes]);
                $emp['nama'] = $namaSes;
            }

            // Pastikan kolom email terisi (kalau row lama belum ada email)
            if (empty($emp['email'])) {
                $m->update($emp['id'], ['email' => $email]);
                $emp['email'] = $email;
            }

            // Sinkronkan session → ikut nama dari employees (supaya tidak “balik” lagi)
            if (!empty($emp['nama'])) {
                session()->set('nama', (string) $emp['nama']);
                session()->set('user_name', (string) $emp['nama']); // kompat lama
            }

            // Jika tabel punya user_id tapi row belum terkait user_id, tautkan
            if ($hasUserId && $userId > 0 && (int) ($emp['user_id'] ?? 0) !== $userId) {
                $m->update($emp['id'], ['user_id' => $userId]);
            }

            return (int) $emp['id'];
        }

        // Belum ada → buat baru
        $data = [
            'nama' => $namaSes !== '' ? $namaSes : 'Tanpa Nama',
            'email' => $email,
            'status' => 'aktif',
        ];
        if ($hasUserId && $userId > 0) {
            $data['user_id'] = $userId;
        }

        $m->insert($data);
        $id = (int) $m->getInsertID();

        // Session ikut nama baru (agar UI langsung konsisten)
        session()->set('nama', (string) $data['nama']);
        session()->set('user_name', (string) $data['nama']); // kompat lama

        return $id;
    }
}
