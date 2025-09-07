<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<main class="roles-page">
    <header class="roles-head">
        <div class="roles-head__title">
            <h2><i class="fa-solid fa-user-shield"></i> Kelola Role Pengguna</h2>
            <p class="roles-head__muted">Hanya Admin yang dapat mengubah role.</p>
        </div>
        <form method="get" class="roles-filter">
            <input type="text" name="q" value="<?= esc($q ?? '') ?>" placeholder="Cari nama atau email…"
                class="roles-filter__input" />
            <button class="btn btn--primary"><i class="fa-solid fa-search"></i> <span>Cari</span></button>
            <a href="<?= site_url('admin/roles') ?>" class="btn">Reset</a>
        </form>
    </header>

    <section class="card roles-card">
        <div class="card__title"><i class="fa-solid fa-users"></i> Daftar Pengguna</div>

        <div class="table-wrap">
            <table class="table table--flat">
                <thead>
                    <tr>
                        <th class="col-no">No.</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th class="col-role">Role Saat Ini</th>
                        <th class="col-quick">Aksi Cepat</th>
                        <th class="col-manual">Set Role Manual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rows)):
                        $i = 1;
                        foreach ($rows as $u): ?>
                            <?php $curr = strtolower($u['role'] ?? 'user'); ?>
                            <tr>
                                <td class="col-no"><?= $i++ ?></td>
                                <td>
                                    <div class="user-name"><?= esc($u['display_name'] ?? ($u['u_nama'] ?? '-')) ?></div>
                                    <div class="user-id">ID: <?= (int) $u['id'] ?></div>
                                </td>
                                <td><span class="user-email"><?= esc($u['email'] ?? '-') ?></span></td>
                                <td>
                                    <span
                                        class="badge badge--<?= esc($curr) ?>"><?= esc(ucfirst($u['role'] ?? 'User')) ?></span>
                                </td>
                                <td>
                                    <div class="actions actions--quick">
                                        <form method="post" action="<?= route_to('admin_roles_update') ?>"
                                            class="actions__form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <input type="hidden" name="role" value="admin">
                                            <button class="btn btn--sm btn--warn">
                                                <i class="fa-solid fa-user-tie"></i><span>Admin</span>
                                            </button>
                                        </form>

                                        <form method="post" action="<?= route_to('admin_roles_update') ?>"
                                            class="actions__form">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                            <input type="hidden" name="role" value="user">
                                            <button class="btn btn--sm">
                                                <i class="fa-solid fa-user"></i><span>User</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td>
                                    <form method="post" action="<?= route_to('admin_roles_update') ?>"
                                        class="actions actions--manual">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">

                                        <label class="sr-only" for="role-<?= (int) $u['id'] ?>">Role</label>
                                        <select id="role-<?= (int) $u['id'] ?>" name="role" class="select">
                                            <?php foreach (($allowed ?? ['user', 'admin']) as $r): ?>
                                                <option value="<?= esc($r) ?>" <?= $curr === $r ? 'selected' : '' ?>>
                                                    <?= esc(ucfirst($r)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button class="btn btn--sm btn--primary">
                                            <i class="fa-solid fa-floppy-disk"></i><span>Simpan</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="6" class="empty">Belum ada pengguna ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<?= $this->endSection() ?>