<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $r = $row ?? []; ?>

<main class="company-wrap">
    <div class="company-container">
        <!-- Header -->
        <header class="page-head">
            <h2><i class="fa-solid fa-building"></i> Profil Perusahaan</h2>
            <p class="muted">Identitas perusahaan & pemilik yang tampil di website.</p>
        </header>

        <!-- Flash -->
        <?php if ($msg = session()->getFlashdata('success')): ?>
            <div class="alert ok"><i class="fa-regular fa-circle-check"></i> <?= esc($msg) ?></div>
        <?php elseif ($msg = session()->getFlashdata('error')): ?>
            <div class="alert bad"><i class="fa-regular fa-circle-xmark"></i> <?= esc($msg) ?></div>
        <?php endif; ?>

        <form action="<?= site_url('admin/company/save') ?>" method="post" enctype="multipart/form-data"
            id="companyForm">
            <?= csrf_field() ?>

            <!-- Informasi perusahaan -->
            <section class="card">
                <div class="section-head">
                    <div class="icon"><i class="fa-solid fa-circle-info"></i></div>
                    <div>
                        <h3>Informasi Perusahaan</h3>
                        <p class="muted">Nama, deskripsi, alamat, serta peta lokasi.</p>
                    </div>
                </div>

                <div class="grid-two">
                    <div class="panel">
                        <div class="field">
                            <label>Nama Perusahaan</label>
                            <input class="ui-input" name="company_name" value="<?= esc($r['company_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label>Tentang Perusahaan</label>
                            <textarea class="ui-textarea" rows="8" name="company_info"
                                placeholder="Ceritakan profil singkat perusahaan..."><?= esc($r['company_info'] ?? '') ?></textarea>
                        </div>
                        <div class="field">
                            <label>Alamat</label>
                            <input class="ui-input" name="address" value="<?= esc($r['address'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label>Google Maps Embed (iframe)</label>
                            <textarea class="ui-textarea" rows="5" name="map_embed"
                                placeholder='Tempel embed iframe dari Google Maps'><?= esc($r['map_embed'] ?? '') ?></textarea>
                            <p class="hint">Jika tidak punya embed, biarkan kosong.</p>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="field">
                            <label>Nama Pemilik</label>
                            <input class="ui-input" name="owner_name" value="<?= esc($r['owner_name'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label>Subjudul Pemilik</label>
                            <input class="ui-input" name="owner_subtitle" value="<?= esc($r['owner_subtitle'] ?? '') ?>"
                                placeholder="Profesional di bidang ...">
                        </div>
                        <div class="field">
                            <label>Foto Pemilik (JPG/PNG/WebP)</label>
                            <input class="ui-file" type="file" name="owner_photo" id="ownerPhoto" accept="image/*">
                            <div class="photo">
                                <img id="ownerPreview"
                                    src="<?= !empty($r['owner_photo']) ? base_url('images/owner/' . $r['owner_photo']) : '' ?>"
                                    alt="Foto Pemilik" <?= empty($r['owner_photo']) ? 'style="display:none"' : '' ?>>
                            </div>
                            <p class="hint">Disarankan ≤ 2MB, rasio 1:1 agar proporsional.</p>
                        </div>

                        <div class="field">
                            <label>Email</label>
                            <input class="ui-input" type="email" name="social_email"
                                value="<?= esc($r['social_email'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label>Instagram (URL)</label>
                            <input class="ui-input" name="social_instagram"
                                value="<?= esc($r['social_instagram'] ?? '') ?>"
                                placeholder="https://instagram.com/...">
                        </div>
                        <div class="field">
                            <label>WhatsApp (+62...)</label>
                            <input class="ui-input" name="social_whatsapp"
                                value="<?= esc($r['social_whatsapp'] ?? '') ?>" placeholder="62812xxxxxxx">
                        </div>
                        <div class="field">
                            <label>LinkedIn (URL)</label>
                            <input class="ui-input" name="social_linkedin"
                                value="<?= esc($r['social_linkedin'] ?? '') ?>"
                                placeholder="https://linkedin.com/in/...">
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
    /* Layout container & spacing */
    .company-wrap {
        background: #fff
    }

    .company-container {
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

    .page-head .muted {
        margin: .25rem 0 0
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

    /* Card & section head */
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

    /* Buttons */
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

    /* Sticky action bar */
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

    .muted {
        color: #6b7280
    }
</style>

<script>
    (function () {
        const input = document.getElementById('ownerPhoto');
        const img = document.getElementById('ownerPreview');
        input?.addEventListener('change', e => {
            const f = e.target.files?.[0]; if (!f) return;
            const r = new FileReader(); r.onload = ev => { img.src = ev.target.result; img.style.display = 'block'; };
            r.readAsDataURL(f);
        });
    })();
</script>

<?= $this->endSection() ?>