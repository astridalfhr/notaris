<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-user-shield"></i> Kelola Role Pengguna</h2>
            <div class="muted">Hanya Multiuser yang dapat mengubah role.</div>
        </div>

        <div class="card" style="margin-bottom:14px;">
            <form method="get" class="flex items-center gap-2" style="display:flex;gap:10px;align-items:center;">
                <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama atau email…"
                    style="flex:1;padding:10px;border:1px solid #ddd;border-radius:8px;" />
                <button class="btn primary"><i class="fa-solid fa-search"></i> Cari</button>
                <a href="<?= site_url('multiuser/roles') ?>" class="btn">Reset</a>
            </form>
        </div>

        <div class="card">
            <div class="card-title"><i class="fa-solid fa-users"></i> Daftar Pengguna</div>

            <div style="overflow:auto;">
                <div class="table-responsive">
                    <table class="table flat">
                        <thead>
                            <tr>
                                <th style="width:56px;">No.</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th style="width:160px;">Role Saat Ini</th>
                                <th style="width:420px;">Aksi Cepat</th>
                                <th style="width:280px;">Set Role Manual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows)):
                                $i = 1;
                                foreach ($rows as $u): ?>
                                    <?php $curr = strtolower($u['role'] ?? 'user'); ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td>
                                            <div style="font-weight:600;">
                                                <?= esc($u['display_name'] ?? ($u['u_nama'] ?? '-')) ?>
                                            </div>
                                            <div class="muted" style="font-size:12px;">ID: <?= (int) $u['id'] ?></div>
                                        </td>
                                        <td><?= esc($u['email'] ?? '-') ?></td>
                                        <td><span
                                                class="badge <?= esc($curr) ?>"><?= esc(ucfirst($u['role'] ?? 'User')) ?></span>
                                        </td>
                                        <td>
                                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                                <form method="post" action="<?= route_to('multiuser_roles_update') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                    <input type="hidden" name="role" value="admin">
                                                    <button class="btn small warn">
                                                        <i class="fa-solid fa-user-tie"></i> Admin
                                                    </button>
                                                </form>
                                                <form method="post" action="<?= route_to('multiuser_roles_update') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                    <input type="hidden" name="role" value="multiuser">
                                                    <button class="btn small success">
                                                        <i class="fa-solid fa-users-gear"></i> Multiuser
                                                    </button>
                                                </form>
                                                <form method="post" action="<?= route_to('multiuser_roles_update') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                    <input type="hidden" name="role" value="user">
                                                    <button class="btn small">
                                                        <i class="fa-solid fa-user"></i> User
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="post" action="<?= route_to('multiuser_roles_update') ?>"
                                                style="display:flex;gap:10px;align-items:center;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                                <select name="role"
                                                    style="padding:10px;border:1px solid #ddd;border-radius:8px;min-width:140px;">
                                                    <?php foreach (($allowed ?? ['user', 'multiuser', 'admin']) as $r): ?>
                                                        <option value="<?= esc($r) ?>" <?= $curr === $r ? 'selected' : '' ?>>
                                                            <?= esc(ucfirst($r)) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn small primary">
                                                    <i class="fa-solid fa-floppy-disk"></i> Simpan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="muted" style="text-align:center;">Belum ada pengguna ditemukan.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>