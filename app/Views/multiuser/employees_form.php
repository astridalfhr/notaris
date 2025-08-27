<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>
    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-id-card-clip"></i> <?= ($mode ?? 'create') === 'edit' ? 'Edit' : 'Tambah' ?> Karyawan
            </h2>
        </div>

        <?php $r = $row ?? []; ?>

        <form
            action="<?= ($mode ?? 'create') === 'edit' ? site_url('multiuser/employees/update/' . $r['id']) : site_url('multiuser/employees/store') ?>"
            method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-circle-info"></i> Data Karyawan</h3>
                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Nama</label>
                        <input name="nama" value="<?= esc($r['nama'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Email</label>
                        <input name="email" value="<?= esc($r['email'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Jabatan</label>
                        <input name="jabatan" value="<?= esc($r['jabatan'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Spesialisasi</label>
                        <input name="spesialisasi" value="<?= esc($r['spesialisasi'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Deskripsi</label>
                        <textarea name="deskripsi" rows="5"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= esc($r['deskripsi'] ?? '') ?></textarea>
                    </div>

                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Foto (jpg/png)</label>
                        <input type="file" name="foto" accept="image/*"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <?php if (!empty($r['foto'])): ?>
                            <div style="margin-top:8px;"><img src="<?= base_url('images/karyawan/' . $r['foto']) ?>"
                                    style="max-height:120px;border-radius:8px;"></div>
                        <?php endif; ?>

                        <div style="height:8px"></div>
                        <label class="muted">Status</label>
                        <select name="status" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                            <option value="aktif" <?= ($r['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= ($r['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif
                            </option>
                        </select>

                        <div style="height:8px"></div>
                        <label class="muted">Tautkan ke User (opsional)</label>
                        <input type="number" name="user_id" value="<?= esc($r['user_id'] ?? '') ?>"
                            placeholder="ID User"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div class="muted" style="font-size:12px;margin-top:6px;">Isi bila karyawan ini juga punya akun
                            login (user_id). Tidak wajib.</div>
                    </div>
                </div>
            </div>

            <div class="card" style="display:flex;justify-content:flex-end;gap:10px;">
                <a href="<?= site_url('multiuser/employees') ?>" class="btn"
                    style="border:1px solid #E5E7EB;padding:10px 14px;border-radius:10px;text-decoration:none;color:#111827;">Batal</a>
                <button class="btn success"
                    style="background:#16A34A;color:#fff;border:none;padding:10px 16px;border-radius:10px;">Simpan</button>
            </div>
        </form>
    </main>
</div>
<?= $this->endSection() ?>