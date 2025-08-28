<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-brand"><i class="fa-solid fa-crown"></i><span>Multi-User Panel</span></div>
        <nav class="admin-nav">
            <a class="admin-nav__link" href="<?= site_url('multi') ?>"><i
                    class="fa-solid fa-gauge"></i><span>Dashboard</span></a>
            <a class="admin-nav__link is-active" href="<?= site_url('multi/users') ?>"><i
                    class="fa-solid fa-users-gear"></i><span>Kelola Akun</span></a>
            <a class="admin-nav__link" href="<?= site_url('multi/employees') ?>"><i
                    class="fa-solid fa-id-card"></i><span>Profil Karyawan</span></a>
            <a class="admin-nav__link" href="<?= site_url('multi/kerja') ?>"><i
                    class="fa-solid fa-briefcase"></i><span>Halaman Kerja</span></a>
        </nav>
    </aside>

    < class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-users-gear"></i> Kelola Peran Akun</h2>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)):
                        $i = 1;
                        foreach ($users as $u): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= esc($u['nama'] ?? '-') ?></td>
                                <td><?= esc($u['email'] ?? '-') ?></td>
                                <td>
                                    <form action="<?= site_url('multi/users/role/' . $u['id']) ?>" method="post"
                                        class="inline-form">
                                        <?= csrf_field() ?>
                                        <select name="role">
                                            <?php $r = strtolower($u['role'] ?? 'user'); ?>
                                            <option value="user" <?= $r === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $r === 'admin' ? 'selected' : '' ?>>Admin (Pegawai)</option>
                                            <option value="multi_user" <?= $r === 'multi_user' ? 'selected' : '' ?>>Multi-User
                                            </option>
                                        </select>
                                        <button type="submit" class="btn small">Simpan</button>
                                    </form>
                                </td>
                                <td><span class="muted">—</span></td>
                            </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="5" class="empty">Tidak ada data.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </main>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>