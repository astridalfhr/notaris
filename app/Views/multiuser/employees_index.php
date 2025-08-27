<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-users-gear"></i> Kelola Karyawan</h2>
            <a class="btn success" href="<?= site_url('multiuser/employees/create') ?>"
                style="background:#16A34A;color:#fff;border:none;padding:10px 14px;border-radius:10px;text-decoration:none;">+
                Tambah Karyawan</a>
        </div>

        <?php if ($msg = session()->getFlashdata('success')): ?>
            <div class="card" style="border-left:4px solid #10B981;"><?= esc($msg) ?></div>
        <?php elseif ($msg = session()->getFlashdata('error')): ?>
            <div class="card" style="border-left:4px solid #EF4444;"><?= esc($msg) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3 class="card-title"><i class="fa-solid fa-list"></i> Daftar Karyawan</h3>
            <table class="table flat">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Email</th>
                        <th>Spesialisasi</th>
                        <th>Status</th>
                        <th style="width:220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $r): ?>
                        <tr>
                            <td>
                                <?php if (!empty($r['foto'])): ?>
                                    <img src="<?= base_url('images/karyawan/' . $r['foto']) ?>" alt=""
                                        style="height:42px;border-radius:8px;">
                                <?php else: ?>
                                    <div class="muted">—</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= esc($r['nama'] ?? '-') ?></strong></td>
                            <td><?= esc($r['jabatan'] ?? '-') ?></td>
                            <td class="muted"><?= esc($r['email'] ?? '-') ?></td>
                            <td><?= esc($r['spesialisasi'] ?? '-') ?></td>
                            <td><?= ($r['status'] ?? 'aktif') === 'aktif' ? '<span class="badge success">Aktif</span>' : '<span class="badge">Nonaktif</span>' ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a class="btn small warn"
                                    href="<?= site_url('multiuser/employees/edit/' . $r['id']) ?>">Edit</a>
                                <form action="<?= site_url('multiuser/employees/toggle/' . $r['id']) ?>" method="post"
                                    style="display:inline"><?= csrf_field() ?><button
                                        class="btn small"><?= ($r['status'] ?? 'aktif') === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                </form>
                                <form action="<?= site_url('multiuser/employees/delete/' . $r['id']) ?>" method="post"
                                    style="display:inline" onsubmit="return confirm('Hapus karyawan ini?')">
                                    <?= csrf_field() ?><button class="btn small danger">Hapus</button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7" class="muted">Belum ada karyawan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?= $this->endSection() ?>