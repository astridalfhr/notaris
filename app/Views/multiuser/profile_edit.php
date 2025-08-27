<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>
    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-user-gear"></i> Profil Multi-User</h2>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="card" style="border-left:4px solid #10B981;"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php elseif (session()->getFlashdata('error')): ?>
            <div class="card" style="border-left:4px solid #EF4444;"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form class="card" action="<?= site_url('multiuser/profile/update') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-row"><label>Nama</label><input type="text" name="nama"
                    value="<?= esc($emp['nama'] ?? '') ?>" required></div>
            <div class="form-row"><label>Email</label><input type="email" name="email"
                    value="<?= esc($emp['email'] ?? '') ?>"></div>
            <div class="form-row"><label>No. Telepon</label><input type="text" name="no_telepon"
                    value="<?= esc($emp['no_telepon'] ?? '') ?>"></div>
            <div class="form-row"><label>Jabatan</label><input type="text" name="jabatan"
                    value="<?= esc($emp['jabatan'] ?? '') ?>"></div>
            <div class="form-row"><label>Spesialisasi</label><input type="text" name="spesialisasi"
                    value="<?= esc($emp['spesialisasi'] ?? '') ?>"></div>
            <div class="form-row">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="4"
                    placeholder="Tuliskan bio singkat..."><?= esc($emp['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="form-row"><label>Status</label>
                <select name="status">
                    <?php $st = strtolower($emp['status'] ?? 'aktif'); ?>
                    <option value="aktif" <?= $st === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="nonaktif" <?= $st === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="form-row"><label>Foto</label><input type="file" name="foto" accept="image/*"></div>

            <?php if (!empty($emp['foto'])): ?>
                <div class="form-row">
                    <label>&nbsp;</label>
                    <img src="<?= base_url('images/karyawan/' . $emp['foto']) ?>" alt="Foto"
                        style="width:100px;height:100px;border-radius:8px;object-fit:cover;border:1px solid #eee;">
                </div>
            <?php endif; ?>

            <button class="btn-grad" type="submit">Simpan</button>
        </form>
    </main>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>