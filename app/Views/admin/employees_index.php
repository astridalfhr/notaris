<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php helper('employee'); // specs_to_string() ?>

<main class="admin-main employees-page">

    <!-- Header atas -->
    <header class="emp-head"
        style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px">
        <h2 style="display:flex;align-items:center;gap:10px;margin:0;font-weight:700">
            <i class="fa-solid fa-users-gear"></i>
            <span>Kelola Karyawan</span>
        </h2>
        <a class="btn btn--success" href="<?= site_url('admin/employees/create') ?>"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:10px;background:#10b981;color:#fff;text-decoration:none;border:1px solid #10b981">
            <i class="fa-solid fa-circle-plus"></i><span>Tambah Karyawan</span>
        </a>
    </header>

    <!-- Kartu tabel -->
    <section class="card emp-card" style="border:1px solid #e5e7eb;border-radius:12px;background:#fff">
        <div class="card-title"
            style="font-weight:700;margin:0;padding:14px 16px;border-bottom:1px solid #eef0f3;display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-list"></i> <span>Daftar Karyawan</span>
        </div>

        <div class="table-wrap" style="overflow:auto">
            <table class="table flat" style="width:100%;border-collapse:separate;border-spacing:0">
                <thead>
                    <tr>
                        <th
                            style="text-align:left;padding:12px 16px;border-bottom:1px solid #eef0f3;background:#fafbfc;width:76px">
                            Foto</th>
                        <th
                            style="text-align:left;padding:12px 16px;border-bottom:1px solid #eef0f3;background:#fafbfc;min-width:220px">
                            Nama</th>
                        <th
                            style="text-align:left;padding:12px 16px;border-bottom:1px solid #eef0f3;background:#fafbfc;min-width:140px">
                            Jabatan</th>
                        <th
                            style="text-align:left;padding:12px 16px;border-bottom:1px solid #eef0f3;background:#fafbfc;min-width:220px">
                            Email</th>
                        <th
                            style="text-align:left;padding:12px 16px;border-bottom:1px solid #eef0f3;background:#fafbfc;min-width:420px">
                            Spesialisasi</th>
                        <th
                            style="text-align:left;padding:12px 16px;border-bottom:1px solid #eef0f3;background:#fafbfc;width:100px">
                            Status</th>
                        <th
                            style="text-align:left;padding:12px 16px;border-bottom:1px solid #eef0f3;background:#fafbfc;width:300px">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $r):
                            $id = (int) ($r['id'] ?? 0);
                            $nama = trim((string) ($r['nama'] ?? '-'));
                            $email = trim((string) ($r['email'] ?? '-'));
                            $jab = trim((string) ($r['jabatan'] ?? '-'));
                            $specs = specs_to_string($r['spesialisasi'] ?? '');
                            $st = strtolower((string) ($r['status'] ?? 'aktif'));
                            $isAct = ($st === 'aktif');

                            // avatar resolver
                            $avatar = '';
                            if (!empty($r['foto'])) {
                                foreach (['images/karyawan/', 'uploads/employees/', 'uploads/profiles/'] as $folder) {
                                    $rel = $folder . $r['foto'];
                                    $abs = FCPATH . $rel;
                                    if (is_file($abs)) {
                                        $avatar = base_url($rel) . '?v=' . (filemtime($abs) ?: time());
                                        break;
                                    }
                                }
                            }
                            ?>
                            <tr style="border-bottom:1px solid #f3f4f6">
                                <!-- Foto -->
                                <td style="padding:14px 16px">
                                    <div
                                        style="width:44px;height:44px;border-radius:999px;overflow:hidden;background:#f3f4f6;display:flex;align-items:center;justify-content:center">
                                        <?php if ($avatar): ?>
                                            <img src="<?= esc($avatar) ?>" alt="<?= esc($nama) ?>"
                                                style="width:100%;height:100%;object-fit:cover">
                                        <?php else: ?>
                                            <i class="fa-regular fa-user" style="color:#9ca3af;font-size:18px"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Nama + sub email kecil -->
                                <td style="padding:14px 16px">
                                    <div style="font-weight:700;margin-bottom:2px"><?= esc($nama) ?></div>
                                    <div style="color:#6b7280;font-size:12px;display:flex;align-items:center;gap:6px">
                                        <i class="fa-solid fa-at" style="font-size:11px"></i>
                                        <span><?= esc($email) ?></span>
                                    </div>
                                </td>

                                <!-- Jabatan -->
                                <td style="padding:14px 16px"><?= esc($jab) ?></td>

                                <!-- Email kolom terpisah (sesuai SS kedua masih ditampilkan) -->
                                <td style="padding:14px 16px"><?= esc($email) ?></td>

                                <!-- Spesialisasi: multiline wrap, tinggi lega -->
                                <td style="padding:14px 16px;white-space:normal;line-height:1.45;color:#111827">
                                    <?= esc($specs) ?>
                                </td>

                                <!-- Status pill -->
                                <td style="padding:14px 16px">
                                    <?php if ($isAct): ?>
                                        <span
                                            style="display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0">Aktif</span>
                                    <?php else: ?>
                                        <span
                                            style="display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#fee2e2;color:#991b1b;border:1px solid #fecaca">Nonaktif</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi -->
                                <td style="padding:12px 16px">
                                    <div style="display:flex;gap:10px;flex-wrap:wrap">
                                        <a class="btn btn--sm" href="<?= site_url('admin/employees/edit/' . $id) ?>"
                                            style="display:inline-flex;gap:6px;align-items:center;padding:8px 12px;border-radius:10px;background:#f3f4f6;border:1px solid #e5e7eb;text-decoration:none;color:#111827">
                                            <i class="fa-solid fa-pen-to-square"></i><span>Edit</span>
                                        </a>

                                        <form action="<?= site_url('admin/employees/toggle/' . $id) ?>" method="post"
                                            class="inline">
                                            <?= csrf_field() ?>
                                            <?php if ($isAct): ?>
                                                <button class="btn btn--sm"
                                                    style="display:inline-flex;gap:6px;align-items:center;padding:8px 12px;border-radius:10px;background:#f3f4f6;border:1px solid #e5e7eb;color:#111827">
                                                    <i class="fa-solid fa-user-slash"></i><span>Nonaktifkan</span>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn--sm"
                                                    style="display:inline-flex;gap:6px;align-items:center;padding:8px 12px;border-radius:10px;background:#10b981;border:1px solid #10b981;color:#fff">
                                                    <i class="fa-solid fa-user-check"></i><span>Aktifkan</span>
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <form action="<?= site_url('admin/employees/delete/' . $id) ?>" method="post"
                                            class="inline" onsubmit="return confirm('Hapus karyawan ini?')">
                                            <?= csrf_field() ?>
                                            <button class="btn btn--sm"
                                                style="display:inline-flex;gap:6px;align-items:center;padding:8px 12px;border-radius:10px;background:#ef4444;border:1px solid #ef4444;color:#fff">
                                                <i class="fa-solid fa-trash-can"></i><span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="7" class="empty"
                                style="text-align:center;color:#9ca3af;padding:22px 10px;font-style:italic">
                                Belum ada karyawan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<!-- Ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<?= $this->endSection() ?>