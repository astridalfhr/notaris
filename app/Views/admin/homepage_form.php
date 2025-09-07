<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $d = $data ?? []; ?>

<main class="homepage-wrap">
    <div class="homepage-container">
        <!-- Header -->
        <header class="page-head">
            <h2><i class="fa-solid fa-newspaper"></i> Kelola Beranda</h2>
            <p class="muted">Profil pemilik, info perusahaan, sosial media, & lokasi.</p>
        </header>

        <!-- Flash -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert ok"><i class="fa-regular fa-circle-check"></i> <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php elseif (session()->getFlashdata('error')): ?>
            <div class="alert bad"><i class="fa-regular fa-circle-xmark"></i> <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('admin/homepage/save') ?>" method="post" enctype="multipart/form-data" id="homeForm">
            <?= csrf_field() ?>

            <!-- Pemilik & Perusahaan -->
            <section class="card">
                <div class="section-head">
                    <div class="icon"><i class="fa-solid fa-user-tie"></i></div>
                    <div>
                        <h3>Profil Pemilik & Perusahaan</h3>
                        <p class="muted">Data ini akan ditampilkan pada halaman beranda.</p>
                    </div>
                </div>

                <div class="grid-two">
                    <!-- Kiri -->
                    <div class="panel">
                        <div class="field">
                            <label>Nama Pemilik</label>
                            <input class="ui-input" name="owner_name" value="<?= esc($d['owner_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label>Deskripsi Singkat</label>
                            <input class="ui-input" name="owner_subtitle" value="<?= esc($d['owner_subtitle'] ?? '') ?>"
                                placeholder="Profesional di bidang …">
                        </div>
                        <div class="field">
                            <label>Foto Pemilik (JPG/PNG/WebP)</label>
                            <input class="ui-file" type="file" name="owner_photo" id="ownerHomePhoto" accept="image/*">
                            <div class="photo">
                                <img id="ownerHomePreview"
                                    src="<?= !empty($d['owner_photo']) ? base_url('images/owner/' . $d['owner_photo']) : '' ?>"
                                    alt="Foto Pemilik" <?= empty($d['owner_photo']) ? 'style="display:none"' : '' ?>>
                            </div>
                            <p class="hint">Ukuran disarankan ≤ 2MB, rasio 1:1.</p>
                        </div>
                    </div>

                    <!-- Kanan -->
                    <div class="panel">
                        <div class="field">
                            <label>Judul Tentang Perusahaan</label>
                            <input class="ui-input" name="about_title" value="<?= esc($d['about_title'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label>Isi Visi & Misi Perusahaan</label>
                            <textarea class="ui-textarea" rows="10" name="about_body"
                                placeholder="Tulis secara paragraf; tidak perlu tag HTML."><?= esc($d['about_body'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sosial media -->
            <section class="card">
                <div class="section-head">
                    <div class="icon"><i class="fa-brands fa-hashtag"></i></div>
                    <div>
                        <h3>Sosial Media & Kontak</h3>
                        <p class="muted">Tautan akan ditampilkan sebagai ikon di beranda.</p>
                    </div>
                </div>

                <div class="grid-two">
                    <div class="panel">
                        <div class="field">
                            <label>Instagram (URL)</label>
                            <input class="ui-input" name="social_instagram"
                                value="<?= esc($d['social_instagram'] ?? '') ?>"
                                placeholder="https://instagram.com/username">
                        </div>
                        <div class="field">
                            <label>WhatsApp (628xx…)</label>
                            <input class="ui-input" name="social_whatsapp"
                                value="<?= esc($d['social_whatsapp'] ?? '') ?>" placeholder="62812xxxxxxx">
                        </div>
                    </div>
                    <div class="panel">
                        <div class="field">
                            <label>Email</label>
                            <input class="ui-input" type="email" name="social_email"
                                value="<?= esc($d['social_email'] ?? '') ?>" placeholder="email@domain.com">
                        </div>
                        <div class="field">
                            <label>LinkedIn (URL)</label>
                            <input class="ui-input" name="social_linkedin"
                                value="<?= esc($d['social_linkedin'] ?? '') ?>"
                                placeholder="https://linkedin.com/in/username">
                        </div>
                    </div>
                </div>
            </section>

            <!-- Lokasi -->
            <section class="card">
                <div class="section-head">
                    <div class="icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div>
                        <h3>Lokasi Kantor</h3>
                        <p class="muted">Alamat & tautan Google Maps.</p>
                    </div>
                </div>

                <div class="grid-two">
                    <div class="panel">
                        <div class="field">
                            <label>Alamat</label>
                            <input class="ui-input" name="address" value="<?= esc($d['address'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="panel">
                        <div class="field">
                            <label>Link Google Maps</label>
                            <input class="ui-input" type="url" name="map_url" value="<?= esc($d['map_url'] ?? '') ?>"
                                placeholder="https://maps.app.goo.gl/...">
                            <p class="hint">Gunakan <em>Share → Copy link</em> dari Google Maps.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sticky actions -->
            <div class="sticky-actions">
                <div class="inner">
                    <a href="<?= site_url('beranda') ?>" class="btn btn--ghost">Batal</a>
                    <button class="btn btn--primary"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</main>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
    .homepage-container {
        max-width: 1100px;
        margin: 26px auto;
        padding: 0 18px
    }

    .page-head {
        margin-bottom: 14px
    }

    .page-head h2 {
        margin: 0;
        display: flex;
        gap: .6rem;
        align-items: center
    }

    .muted {
        color: #6b7280
    }

    /* Alerts */
    .alert {
        padding: 10px 12px;
        border-radius: 10px;
        margin: 12px 0;
        display: flex;
        gap: .5rem;
        align-items: center;
        font-size: .95rem
    }

    .alert.ok {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0
    }

    .alert.bad {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca
    }

    /* Cards */
    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 0 rgba(17, 24, 39, .03)
    }

    .section-head {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 12px
    }

    .section-head .icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        color: #4f46e5
    }

    /* Grid */
    .grid-two {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px
    }

    @media(max-width:1000px) {
        .grid-two {
            grid-template-columns: 1fr
        }
    }

    .panel {
        background: #fafafa;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px
    }

    /* Fields */
    .field {
        margin-bottom: 14px
    }

    .field label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px
    }

    .ui-input,
    .ui-textarea,
    .ui-file {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 11px 12px;
        font-size: 14.5px;
        background: #fff;
        outline: none
    }

    .ui-input:focus,
    .ui-textarea:focus {
        border-color: #a78bfa;
        box-shadow: 0 0 0 3px rgba(167, 139, 250, .15)
    }

    .ui-textarea {
        resize: vertical
    }

    .hint {
        margin-top: 6px;
        color: #6b7280;
        font-size: .88rem
    }

    /* Photo preview */
    .photo {
        margin-top: 10px
    }

    .photo img {
        max-width: 180px;
        max-height: 180px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        object-fit: cover;
        display: block
    }

    /* Buttons & sticky bar */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        color: #111827;
        text-decoration: none;
        background: #fff
    }

    .btn--ghost {
        background: #fff
    }

    .btn--primary {
        background: linear-gradient(180deg, #8b5cf6, #7c3aed);
        color: #fff;
        border: none
    }

    .sticky-actions {
        position: sticky;
        bottom: 0;
        z-index: 5;
        margin-top: 18px
    }

    .sticky-actions .inner {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffffcc;
        backdrop-filter: saturate(180%) blur(8px)
    }
</style>

<script>
    (function () {
        const input = document.getElementById('ownerHomePhoto');
        const img = document.getElementById('ownerHomePreview');
        input?.addEventListener('change', e => {
            const f = e.target.files?.[0]; if (!f) return;
            const r = new FileReader(); r.onload = ev => { img.src = ev.target.result; img.style.display = 'block'; };
            r.readAsDataURL(f);
        });
    })();
</script>

<?= $this->endSection() ?>