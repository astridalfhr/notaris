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
}
