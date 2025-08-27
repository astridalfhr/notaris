<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-building"></i> Profil Perusahaan</h2>
        </div>

        <?php if ($msg = session()->getFlashdata('success')): ?>
            <div class="card" style="border-left:4px solid #10B981;"><?= esc($msg) ?></div>
        <?php endif; ?>

        <?php $r = $row ?? []; ?>
        <form action="<?= site_url('multiuser/company/save') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-circle-info"></i> Informasi Perusahaan</h3>
                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Nama Perusahaan</label>
                        <input name="company_name" value="<?= esc($r['company_name'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Tentang Perusahaan</label>
                        <textarea name="company_info" rows="7"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= esc($r['company_info'] ?? '') ?></textarea>

                        <div style="height:8px"></div>
                        <label class="muted">Alamat</label>
                        <input name="address" value="<?= esc($r['address'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Google Maps Embed (iframe)</label>
                        <textarea name="map_embed" rows="4"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= esc($r['map_embed'] ?? '') ?></textarea>
                    </div>

                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Nama Pemilik</label>
                        <input name="owner_name" value="<?= esc($r['owner_name'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Subjudul Pemilik</label>
                        <input name="owner_subtitle" value="<?= esc($r['owner_subtitle'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Foto Pemilik</label>
                        <input type="file" name="owner_photo" accept="image/*"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <?php if (!empty($r['owner_photo'])): ?>
                            <div style="margin-top:8px;"><img src="<?= base_url('images/owner/' . $r['owner_photo']) ?>"
                                    style="max-height:120px;border-radius:8px;"></div>
                        <?php endif; ?>

                        <div style="height:8px"></div>
                        <label class="muted">Email</label>
                        <input name="social_email" value="<?= esc($r['social_email'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">Instagram (URL)</label>
                        <input name="social_instagram" value="<?= esc($r['social_instagram'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">WhatsApp (+62...)</label>
                        <input name="social_whatsapp" value="<?= esc($r['social_whatsapp'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">

                        <div style="height:8px"></div>
                        <label class="muted">LinkedIn (URL)</label>
                        <input name="social_linkedin" value="<?= esc($r['social_linkedin'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                    </div>

                </div>
            </div>

            <div class="card" style="display:flex;justify-content:flex-end;gap:10px;">
                <a href="<?= site_url('multiuser') ?>" class="btn"
                    style="border:1px solid #E5E7EB;padding:10px 14px;border-radius:10px;text-decoration:none;color:#111827;">Kembali</a>
                <button class="btn success"
                    style="background:#16A34A;color:#fff;border:none;padding:10px 16px;border-radius:10px;">Simpan</button>
            </div>
        </form>
    </main>
</div>
<?= $this->endSection() ?>