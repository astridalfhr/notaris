<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>
    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-pen-to-square"></i> <?= ($mode ?? 'create') === 'edit' ? 'Edit' : 'Tambah' ?> Banner
            </h2>
        </div>

        <?php $r = $row ?? []; ?>
        <form
            action="<?= ($mode ?? 'create') === 'edit' ? site_url('multiuser/hero/update/' . $r['id']) : site_url('multiuser/hero/store') ?>"
            method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-circle-info"></i> Informasi Banner</h3>
                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Judul</label>
                        <input name="title" value="<?= esc($r['title'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Tagline</label>
                        <input name="tagline" value="<?= esc($r['tagline'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Teks Tombol (opsional)</label>
                        <input name="button_text" value="<?= esc($r['button_text'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Link Tombol (opsional)</label>
                        <input name="button_link" value="<?= esc($r['button_link'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                    </div>

                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Gambar (jpg/png)</label>
                        <input type="file" name="image" accept="image/*"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <?php if (!empty($r['image'])): ?>
                            <div style="margin-top:8px;"><img src="<?= base_url('images/hero/' . $r['image']) ?>"
                                    style="max-height:120px;border-radius:8px;"></div>
                        <?php endif; ?>
                        <div style="height:8px"></div>
                        <label class="muted">Urutan</label>
                        <input type="number" name="sort_order" value="<?= esc((string) ($r['sort_order'] ?? 0)) ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label><input type="checkbox" name="is_active" value="1" <?= !isset($r['is_active']) || $r['is_active'] ? 'checked' : '' ?>> Aktif</label>
                    </div>
                </div>
            </div>

            <div class="card" style="display:flex;justify-content:flex-end;gap:10px;">
                <a href="<?= site_url('multiuser/hero') ?>" class="btn"
                    style="border:1px solid #E5E7EB;padding:10px 14px;border-radius:10px;text-decoration:none;color:#111827;">Batal</a>
                <button class="btn success"
                    style="background:#16A34A;color:#fff;border:none;padding:10px 16px;border-radius:10px;">Simpan</button>
            </div>
        </form>
    </main>
</div>
<?= $this->endSection() ?>