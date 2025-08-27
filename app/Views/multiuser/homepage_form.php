<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>
    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-newspaper"></i> Kelola Beranda</h2>
            <div class="muted">Profil pemilik, info perusahaan, sosial media, & lokasi.</div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="card" style="border-left:4px solid #10B981;"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php elseif (session()->getFlashdata('error')): ?>
            <div class="card" style="border-left:4px solid #EF4444;"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?php $d = $data ?? []; ?>
        <form action="<?= site_url('multiuser/homepage/save') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <!-- PROFIL PEMILIK + PERUSAHAAN -->
            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-user-tie"></i> Profil Pemilik & Perusahaan</h3>
                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Nama Pemilik</label>
                        <input name="owner_name" value="<?= esc($d['owner_name'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Deskripsi Singkat</label>
                        <input name="owner_subtitle" value="<?= esc($d['owner_subtitle'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Foto Pemilik (jpg/png)</label>
                        <input type="file" name="owner_photo" accept="image/*"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <?php if (!empty($d['owner_photo'])): ?>
                            <div style="margin-top:8px;"><img src="<?= base_url('images/owner/' . $d['owner_photo']) ?>"
                                    style="max-height:80px;border-radius:8px;"></div>
                        <?php endif; ?>
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Judul Tentang Perusahaan</label>
                        <input name="about_title" value="<?= esc($d['about_title'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Isi Visi & Misi Perusahaan</label>
                        <textarea name="about_body" rows="6"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= esc($d['about_body'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Nama Perusahaan</label>
                        <input name="company_name" value="<?= esc($d['company_name'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div style="height:8px"></div>
                        <label class="muted">Info Perusahaan</label>
                        <textarea name="company_info" rows="5"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;"><?= esc($d['company_info'] ?? '') ?></textarea>
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Sosial Media & Kontak</label>
                        <input name="social_instagram" placeholder="Instagram URL"
                            value="<?= esc($d['social_instagram'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin-bottom:8px;">
                        <input name="social_whatsapp" placeholder="628xx..."
                            value="<?= esc($d['social_whatsapp'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin-bottom:8px;">
                        <input name="social_email" placeholder="email@domain.com"
                            value="<?= esc($d['social_email'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin-bottom:8px;">
                        <input name="social_linkedin" placeholder="LinkedIn URL"
                            value="<?= esc($d['social_linkedin'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                    </div>
                </div>
            </div>

            <!-- LOKASI -->
            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-location-dot"></i> Lokasi Kantor</h3>
                <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Alamat</label>
                        <input name="address" value="<?= esc($d['address'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;">
                        <label class="muted">Link Google Maps</label>
                        <input type="url" name="map_url" value="<?= esc($d['map_url'] ?? '') ?>"
                            style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;">
                        <div class="muted" style="margin-top:6px;font-size:12px;">Tempel link Google Maps (contoh:
                            https://maps.app.goo.gl/... atau https://www.google.com/maps/place/...)</div>
                    </div>

                </div>
            </div>

            <div class="card" style="display:flex;justify-content:flex-end;gap:10px;">
                <a href="<?= site_url('multiuser') ?>" class="btn"
                    style="border:1px solid #E5E7EB;padding:10px 14px;border-radius:10px;text-decoration:none;color:#111827;">Batal</a>
                <button class="btn success"
                    style="background:#16A34A;color:#fff;border:none;padding:10px 16px;border-radius:10px;">Simpan</button>
            </div>
        </form>
    </main>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>