<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-brand"><i class="fa-solid fa-crown"></i><span>Multi-User Panel</span></div>
        <nav class="admin-nav">
            <a class="admin-nav__link" href="<?= site_url('multi') ?>"><i
                    class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
            <a class="admin-nav__link" href="<?= site_url('multi/users') ?>"><i
                    class="fa-solid fa-users-gear"></i><span>Kelola Akun</span></a>
            <a class="admin-nav__link is-active" href="<?= site_url('multi/employees') ?>"><i
                    class="fa-solid fa-id-card"></i><span>Profil Karyawan</span></a>
            <a class="admin-nav__link" href="<?= site_url('multi/kerja') ?>"><i
                    class="fa-solid fa-briefcase"></i><span>Halaman Kerja</span></a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-id-card"></i> Daftar Karyawan</h2>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employees)):
                        $i = 1;
                        foreach ($employees as $e): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= esc($e['nama'] ?? '-') ?></td>
                                <td><?= esc($e['email'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="3" class="empty">Belum ada data karyawan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>