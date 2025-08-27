<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SiteSettingsModel;

class Profile extends BaseController
{
    public function index()
    {
        $db      = db_connect();
        $builder = $this->buildEmployeesQuery($db);

        $rows = $builder
            ->select("
                u.id            AS user_id,
                u.nama          AS u_nama,
                u.email         AS u_email,
                u.role          AS u_role,
                u.profile_photo AS u_photo,
                e.id            AS emp_id,
                e.nama          AS e_nama,
                e.email         AS e_email,
                e.jabatan       AS jabatan,
                e.spesialisasi  AS spesialisasi,
                e.deskripsi     AS deskripsi,
                e.status        AS e_status,
                e.foto          AS e_foto
            ")
            ->where("COALESCE(e.status, 'aktif') !=", 'nonaktif')
            ->orderBy('COALESCE(e.nama, u.nama) ASC', '', false)
            ->get()->getResultArray();

        $employees = $this->normalizeEmployeeRows($rows);

        // Ambil profil perusahaan dari beberapa sumber secara defensif
        $company = $this->loadCompanyProfileRobust($db);

        return view('profile', [
            'employees' => $employees,
            'company'   => $company,
        ]);
    }

    public function detail($empId = null)
    {
        $empId = (int) $empId;
        if ($empId <= 0) {
            return redirect()->back()->with('error', 'Parameter tidak valid.');
        }

        $db      = db_connect();
        $builder = $this->buildEmployeesQuery($db);

        $row = $builder
            ->select("
                u.id            AS user_id,
                u.nama          AS u_nama,
                u.email         AS u_email,
                u.role          AS u_role,
                u.profile_photo AS u_photo,
                e.id            AS emp_id,
                e.nama          AS e_nama,
                e.email         AS e_email,
                e.jabatan       AS jabatan,
                e.spesialisasi  AS spesialisasi,
                e.deskripsi     AS deskripsi,
                e.status        AS e_status,
                e.foto          AS e_foto
            ")
            ->where('e.id', $empId)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->back()->with('error', 'Karyawan tidak ditemukan.');
        }

        $mapped = $this->normalizeEmployeeRows([$row]);
        $emp    = $mapped[0] ?? null;

        return view('profile_detail', ['employee' => $emp]);
    }

    public function json()
    {
        $db      = db_connect();
        $builder = $this->buildEmployeesQuery($db);

        $rows = $builder
            ->select("
                u.id            AS user_id,
                u.nama          AS u_nama,
                u.email         AS u_email,
                u.role          AS u_role,
                u.profile_photo AS u_photo,
                e.id            AS emp_id,
                e.nama          AS e_nama,
                e.email         AS e_email,
                e.jabatan       AS jabatan,
                e.spesialisasi  AS spesialisasi,
                e.deskripsi     AS deskripsi,
                e.status        AS e_status,
                e.foto          AS e_foto
            ")
            ->where("COALESCE(e.status, 'aktif') !=", 'nonaktif')
            ->orderBy('COALESCE(e.nama, u.nama) ASC', '', false)
            ->get()->getResultArray();

        $employees = $this->normalizeEmployeeRows($rows);

        return $this->response->setJSON([
            'ok'    => true,
            'count' => count($employees),
            'data'  => $employees,
        ]);
    }

    public function all()
    {
        $db      = db_connect();
        $builder = $this->buildEmployeesQuery($db);

        $rows = $builder
            ->select("
                u.id            AS user_id,
                u.nama          AS u_nama,
                u.email         AS u_email,
                u.role          AS u_role,
                u.profile_photo AS u_photo,
                e.id            AS emp_id,
                e.nama          AS e_nama,
                e.email         AS e_email,
                e.jabatan       AS jabatan,
                e.spesialisasi  AS spesialisasi,
                e.deskripsi     AS deskripsi,
                e.status        AS e_status,
                e.foto          AS e_foto
            ")
            ->orderBy('COALESCE(e.nama, u.nama) ASC', '', false)
            ->get()->getResultArray();

        $employees = $this->normalizeEmployeeRows($rows);

        $company = $this->loadCompanyProfileRobust($db);

        return view('profile', [
            'employees' => $employees,
            'company'   => $company,
        ]);
    }

    // ======================== Helpers ========================

    private function buildEmployeesQuery($db)
    {
        $empFields = array_map(static fn($f) => $f->name, $db->getFieldData('employees'));
        $hasUserId = in_array('user_id', $empFields, true);

        $employeesTb = $db->protectIdentifiers('employees');
        $builder     = $db->table("$employeesTb e"); // base: employees

        if ($hasUserId) {
            $builder->join('users u', 'u.id = e.user_id', 'left');
        } else {
            $builder->join('users u', 'u.email = e.email', 'left');
        }

        return $builder;
    }

    private function normalizeEmployeeRows(array $rows): array
    {
        $employees = [];

        foreach ($rows as $r) {
            $nama   = !empty($r['e_nama']) ? $r['e_nama'] : ($r['u_nama'] ?? '');
            $email  = !empty($r['e_email']) ? $r['e_email'] : ($r['u_email'] ?? '');
            $status = strtolower((string) ($r['e_status'] ?? 'aktif'));

            if ($status === 'nonaktif') continue;

            $fotoUrl = '';
            if (!empty($r['e_foto'])) {
                $rel = 'images/karyawan/' . $r['e_foto'];
                $abs = FCPATH . $rel;
                if (is_file($abs)) $fotoUrl = base_url($rel);
            }
            if ($fotoUrl === '' && !empty($r['u_photo']) && filter_var($r['u_photo'], FILTER_VALIDATE_URL)) {
                $fotoUrl = $r['u_photo'];
            }
            if ($fotoUrl === '') $fotoUrl = 'https://via.placeholder.com/150';

            $employees[] = [
                'emp_id'       => (int) ($r['emp_id'] ?? 0),
                'nama'         => $nama !== '' ? $nama : '(Tanpa Nama)',
                'email'        => $email,
                'jabatan'      => (string) ($r['jabatan'] ?? ''),
                'spesialisasi' => (string) ($r['spesialisasi'] ?? ''),
                'deskripsi'    => (string) ($r['deskripsi'] ?? ''),
                'status'       => $status,
                'foto_url'     => $fotoUrl,
            ];
        }

        return $employees;
    }

    /**
     * Loader profil perusahaan yang robust:
     * - Cari di site_settings (konteks 'profile' | 'company' | 'home') dan is_active=1 (jika ada).
     * - Kalau tidak ada, cari di site_home (prioritaskan is_active=1 jika ada).
     * - Pilih row terbaru (updated_at desc, fallback id desc).
     * - Auto-convert map_url → iframe bila map_embed kosong.
     */
    private function loadCompanyProfileRobust($db): array
    {
        // 1) Coba site_settings (beberapa kemungkinan context)
        $company = $this->tryLoadFromSiteSettings($db, ['profile','company','home']);
        if (!$this->hasMeaningfulCompany($company)) {
            // 2) Fallback: site_home (aktif terbaru → atau terbaru)
            $company = $this->tryLoadFromSiteHome($db);
        }

        // 3) Auto-convert map_url → iframe bila map_embed kosong
        if (empty($company['map_embed']) && !empty($company['map_url'])) {
            $src = $this->mapEmbedSrc($company['map_url']);
            if ($src) {
                $company['map_embed'] =
                    '<iframe src="'.esc($src, 'attr').'" width="100%" height="380" style="border:0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
            }
        }

        // 4) Pastikan semua key ada (supaya view aman)
        $company = array_merge([
            'name'             => 'Kantor Notaris',
            'summary'          => '',
            'owner_name'       => '',
            'owner_subtitle'   => '',
            'owner_photo'      => '',
            'address'          => '',
            'map_embed'        => '',
            'map_url'          => '',
            'social_email'     => '',
            'social_instagram' => '',
            'social_whatsapp'  => '',
            'social_linkedin'  => '',
        ], $company);

        return $company;
    }

    private function tryLoadFromSiteSettings($db, array $contexts): array
    {
        // Pastikan tabelnya ada
        if (!$this->tableExists($db, 'site_settings')) return [];

        $m = new SiteSettingsModel();

        // Ambil row terbaru yang context-nya salah satu dari $contexts dan is_active=1 (jika kolom ada)
        $builder = $m->builder();
        $builder->groupStart();
        foreach ($contexts as $i => $ctx) {
            $i === 0 ? $builder->where('context', $ctx) : $builder->orWhere('context', $ctx);
        }
        $builder->groupEnd();

        // cek apakah ada kolom is_active
        $hasIsActive = $this->columnExists($db, 'site_settings', 'is_active');
        if ($hasIsActive) {
            $builder->where('is_active', 1);
        }

        // urutkan terbaru
        if ($this->columnExists($db, 'site_settings', 'updated_at')) {
            $builder->orderBy('updated_at', 'DESC');
        } else {
            $builder->orderBy('id', 'DESC');
        }

        $row = $builder->get()->getRowArray();
        if (!$row) return [];

        return [
            'name'             => $row['company_name']     ?? '',
            'summary'          => $row['company_info']     ?? '',
            'owner_name'       => $row['owner_name']       ?? '',
            'owner_subtitle'   => $row['owner_subtitle']   ?? '',
            'owner_photo'      => !empty($row['owner_photo']) ? base_url('images/owner/'.$row['owner_photo']) : '',
            'address'          => $row['address']          ?? '',
            'map_embed'        => $row['map_embed']        ?? '',
            'map_url'          => $row['map_url']          ?? '',
            'social_email'     => $row['social_email']     ?? '',
            'social_instagram' => $row['social_instagram'] ?? '',
            'social_whatsapp'  => $row['social_whatsapp']  ?? '',
            'social_linkedin'  => $row['social_linkedin']  ?? '',
        ];
    }

    private function tryLoadFromSiteHome($db): array
    {
        if (!$this->tableExists($db, 'site_home')) return [];

        $fields = array_map(static fn($f) => $f->name, $db->getFieldData('site_home'));
        $q = $db->table('site_home');

        if (in_array('is_active', $fields, true)) {
            $q->where('is_active', 1);
        }

        if (in_array('updated_at', $fields, true)) {
            $q->orderBy('updated_at', 'DESC');
        } else {
            $q->orderBy('id', 'DESC');
        }

        $row = $q->get()->getRowArray() ?? [];
        if (!$row) return [];

        return [
            'name'             => $row['company_name']      ?? '',
            'summary'          => $row['company_info']      ?? '',
            'owner_name'       => $row['owner_name']        ?? '',
            'owner_subtitle'   => $row['owner_subtitle']    ?? '',
            'owner_photo'      => !empty($row['owner_photo']) ? base_url('images/owner/'.$row['owner_photo']) : '',
            'address'          => $row['address']           ?? '',
            'map_embed'        => $row['map_embed']         ?? '',
            'map_url'          => $row['map_url']           ?? '',
            'social_email'     => $row['contact_email']     ?? '',
            'social_instagram' => $row['social_instagram']  ?? '',
            'social_whatsapp'  => $row['contact_whatsapp']  ?? '',
            'social_linkedin'  => $row['social_linkedin']   ?? '',
        ];
    }

    private function hasMeaningfulCompany(array $c): bool
    {
        // Ada data "berarti" kalau minimal salah satu dari nama, summary, address non-kosong
        return !empty($c['name']) || !empty($c['summary']) || !empty($c['address']);
    }

    private function tableExists($db, string $table): bool
    {
        try {
            $db->getFieldData($table);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function columnExists($db, string $table, string $column): bool
    {
        try {
            foreach ($db->getFieldData($table) as $f) {
                if ($f->name === $column) return true;
            }
        } catch (\Throwable $e) {}
        return false;
    }

    /** Ubah link Google Maps biasa menjadi URL embed */
    private function mapEmbedSrc(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') return null;

        if (stripos($url, 'output=embed') !== false) return $url;

        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) return null;

        parse_str($parts['query'] ?? '', $q);

        if (!empty($q['q'])) {
            $query = rawurlencode($q['q']);
            return "https://www.google.com/maps?q={$query}&output=embed";
        }

        if (!empty($parts['path']) && preg_match('~/@(-?\d+\.?\d*),(-?\d+\.?\d*)~', $parts['path'], $m)) {
            $pos = $m[1] . ',' . $m[2];
            return "https://www.google.com/maps?q=" . rawurlencode($pos) . "&output=embed";
        }

        if (!empty($parts['path']) && preg_match('~/place/([^/]+)~', $parts['path'], $m)) {
            $place = rawurlencode(str_replace('+', ' ', $m[1]));
            return "https://www.google.com/maps?q={$place}&output=embed";
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'output=embed';
    }
}
