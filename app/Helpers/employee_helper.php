<?php
/**
 * employee_helper.php
 * Helper util karyawan: format spesialisasi & URL foto karyawan.
 * Pastikan url helper aktif (base_url) — biasanya sudah autoload di CI4.
 */

if (!function_exists('specs_to_string')) {
    /**
     * Ubah value spesialisasi (array / JSON string / CSV mentah) jadi string "A, B, C".
     * Contoh input valid:
     *  - ["Akta", "Validasi"]
     *  - '["Akta","Validasi"]'
     *  - 'Akta, Validasi, "Cek Lokasi"'
     */
    function specs_to_string($val): string
    {
        // 1) Jika array, langsung pakai
        if (is_array($val)) {
            $arr = $val;
        }
        // 2) Jika string JSON array
        elseif (is_string($val) && $val !== '' && ($tmp = json_decode($val, true)) && is_array($tmp)) {
            $arr = $tmp;
        }
        // 3) Fallback: CSV / string mentah / angka / null
        else {
            $s = is_string($val) ? $val : (is_null($val) ? '' : (string) $val);
            // buang bracket [ ... ] kalau ada
            $s = preg_replace('/^\s*\[|\]\s*$/', '', $s);
            // pecah koma, trim, buang petik
            $parts = array_map(
                static function ($p) {
                    $p = trim((string) $p);
                    return trim($p, "\"' \t\n\r\0\x0B");
                },
                array_filter(explode(',', $s), static fn($x) => $x !== '' && $x !== null)
            );
            $arr = $parts;
        }

        // unik + buang kosong, map ke string bersih
        $arr = array_values(array_unique(array_filter(array_map('strval', $arr), static fn($x) => trim($x) !== '')));
        return implode(', ', $arr);
    }
}

if (!function_exists('employee_photo_url')) {
    /**
     * Dapatkan URL foto untuk satu record karyawan.
     * Urutan:
     *   1) images/karyawan/{foto} jika ada
     *   2) kolom URL/path lain di $emp: foto_url, profile_photo, google_photo, google_picture, avatar, avatar_url
     *   3) nilai avatar di session (avatar / google_picture)
     *   4) UI Avatars berdasarkan nama/email
     *   5) Gravatar identicon (kalau ada email)
     *   6) Placeholder via.placeholder.com
     */
    function employee_photo_url(array $emp): string
    {
        // Normalisasi ke URL absolut bila input adalah path relatif
        $to_abs = static function (string $u): string {
            $u = trim($u);
            if ($u === '')
                return '';
            if (preg_match('#^https?://#i', $u))
                return $u; // sudah absolut
            if (function_exists('base_url'))
                return base_url(ltrim($u, '/'));
            return '/' . ltrim($u, '/'); // fallback darurat
        };

        // 1) file lokal di images/karyawan
        $local = trim((string) ($emp['foto'] ?? ''));
        if ($local !== '') {
            return function_exists('base_url')
                ? base_url('images/karyawan/' . rawurlencode($local))
                : '/images/karyawan/' . rawurlencode($local);
        }

        // 2) kolom URL/path yang mungkin ada di record karyawan
        foreach (['foto_url', 'profile_photo', 'google_photo', 'google_picture', 'avatar', 'avatar_url'] as $k) {
            $u = trim((string) ($emp[$k] ?? ''));
            if ($u !== '')
                return $to_abs($u);
        }

        // 3) sesi login (kalau avatar disimpan di session)
        $sessVal = '';
        if (function_exists('session')) {
            $sessVal = trim((string) (session('avatar') ?? session('google_picture') ?? ''));
        }
        if ($sessVal !== '')
            return $to_abs($sessVal);

        // 4) UI Avatars pakai nama/email
        $label = trim((string) ($emp['nama'] ?? ($emp['email'] ?? 'User')));
        if ($label !== '') {
            $qs = http_build_query([
                'name' => $label,
                'size' => 200,
                'background' => 'random',
            ]);
            return 'https://ui-avatars.com/api/?' . $qs;
        }

        // 5) Gravatar identicon (kalau ada email)
        $email = strtolower(trim((string) ($emp['email'] ?? '')));
        if ($email !== '') {
            return 'https://www.gravatar.com/avatar/' . md5($email) . '?s=200&d=identicon';
        }

        // 6) mentok: placeholder
        return 'https://via.placeholder.com/200?text=IMG';
    }
}
