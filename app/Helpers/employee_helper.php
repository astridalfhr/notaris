<?php
if (!function_exists('specs_to_string')) {
    function specs_to_string($val): string
    {
        if (is_array($val)) {
            $arr = $val;
        } elseif (is_string($val) && $val !== '' && is_array($tmp = json_decode($val, true))) {
            $arr = $tmp;                           // JSON -> array
        } else {
            $s = (string) $val;                     // fallback CSV/string mentah
            $s = preg_replace('/^\s*\[|\]\s*$/', '', $s);   // buang [ ]
            $parts = array_map(function ($p) {
                $p = trim($p);
                return trim($p, "\"' \t\n\r\0\x0B");        // buang petik
            }, array_filter(explode(',', $s), fn($x) => $x !== '' && $x !== null));
            $arr = $parts;
        }
        $arr = array_values(array_unique(array_filter(array_map('strval', $arr))));
        return implode(', ', $arr);
    }

    if (!function_exists('employee_photo_url')) {
        function employee_photo_url(array $emp): string
        {
            // 1) file lokal
            $local = trim((string) ($emp['foto'] ?? ''));
            if ($local !== '')
                return base_url('images/karyawan/' . rawurlencode($local));

            // 2) url yang sudah disimpan (google_picture/foto_url)
            foreach (['foto_url', 'google_photo', 'google_picture', 'avatar'] as $k) {
                $u = trim((string) ($emp[$k] ?? ''));
                if ($u !== '')
                    return $u;
            }
            $sess = trim((string) (session('avatar') ?? session('google_picture') ?? ''));
            if ($sess !== '')
                return $sess;

            // 3) UI Avatars dari nama/email
            $name = trim((string) ($emp['nama'] ?? ($emp['email'] ?? 'User')));
            if ($name !== '') {
                $qs = http_build_query(['name' => $name, 'size' => 200, 'background' => 'random']);
                return 'https://ui-avatars.com/api/?' . $qs;
            }

            // 4) fallback ke gravatar identicon
            $email = strtolower(trim((string) ($emp['email'] ?? '')));
            if ($email !== '')
                return 'https://www.gravatar.com/avatar/' . md5($email) . '?s=200&d=identicon';

            // 5) placeholder
            return 'https://via.placeholder.com/200?text=IMG';
        }
    }

}
