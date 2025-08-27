<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>
    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-pen-to-square"></i> <?= ($mode ?? 'create') === 'edit' ? 'Edit' : 'Tambah' ?> Berita
            </h2>
        </div>

        <?php $r = $row ?? []; ?>
        <form
            action="<?= ($mode ?? 'create') === 'edit' ? site_url('multiuser/news/update/' . $r['id']) : site_url('multiuser/news/store') ?>"
            method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-info-circle"></i> Informasi</h3>
                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Judul</label>
                        <input name="title" value="<?= esc($r['title'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Ringkasan</label>
                        <textarea name="excerpt" rows="4"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= esc($r['excerpt'] ?? '') ?></textarea>
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Gambar (opsional)</label>
                        <input type="file" name="image" accept="image/*"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <?php if (!empty($r['image'])): ?>
                            <div style="margin-top:8px;"><img src="<?= base_url('images/news/' . $r['image']) ?>"
                                    style="max-height:120px;border-radius:8px;"></div>
                        <?php endif; ?>
                        <div style="height:8px"></div>
                        <label class="muted">Tanggal Publish</label>
                        <input type="datetime-local" name="published_at"
                            value="<?= !empty($r['published_at']) ? date('Y-m-d\TH:i', strtotime($r['published_at'])) : '' ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                    </div>
                </div>
            </div>

            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-file-lines"></i> Isi Berita</h3>
                <textarea name="body" rows="12"
                    style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= esc($r['body'] ?? '') ?></textarea>
            </div>

            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-toggle-on"></i> Pengaturan</h3>
                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label><input type="checkbox" name="is_featured" value="1"
                                <?= !empty($r['is_featured']) ? 'checked' : ''; ?>> Tampilkan sebagai Featured
                            (slider)</label>
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label><input type="checkbox" name="is_published" value="1" <?= (!isset($r['is_published']) || $r['is_published']) ? 'checked' : ''; ?>> Publish</label>
                    </div>
                </div>
            </div>

            <div class="card" style="display:flex;justify-content:flex-end;gap:10px;">
                <a href="<?= site_url('multiuser/news') ?>" class="btn"
                    style="border:1px solid #E5E7EB;padding:10px 14px;border-radius:10px;text-decoration:none;color:#111827;">Batal</a>
                <button class="btn success"
                    style="background:#16A34A;color:#fff;border:none;padding:10px 16px;border-radius:10px;">Simpan</button>
            </div>
        </form>
    </main>
</div>
<?= $this->endSection() ?>