<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$emp = $emp ?? [];
// Opsi spesialis (silakan sesuaikan kalau perlu)
$PPAT_OPTIONS = [
    'Cek Lokasi',
    'Cek Kawasan',
    'Validasi',
    'Alih Wilayah',
    'Pemulihan Data',
    'Roya',
    'Pengecekan Sertipikat',
    'Akta Jual Beli',
    'Akta Hibah',
    'Akta Pembagian Hak Bersama',
    'Turun Waris',
    'Pemisahan',
    'Peningkatan Hak',
    'Pelepasan Hak',
    'Turun Hak',
    'Ubah Lahan Pertanian Jadi Lahan Pekarangan',
    'Laporan Bulanan'
];
$NOTARIS_OPTIONS = [
    'Skmht',
    'Pendirian Pt',
    'Perubahan Pt',
    'Pendirian Yayasan',
    'Perubahan Yayasan',
    'Pendirian Perkumpulan',
    'Perubahan Perkumpulan',
    'Pendirian Perseroan Komanditer',
    'Perubahan Perseroan Komanditer',
    'Pendirian Koperasi',
    'Perubahan Koperasi',
    'Perjanjian Jual Beli',
    'Kuasa Untuk Menjual',
    'Perjanjian - Perjanjian'
];

// Preselect spesialisasi: dukung JSON / CSV
$selectedSpec = [];
if (!empty($emp['spesialisasi'])) {
    $tmp = json_decode((string) $emp['spesialisasi'], true);
    if (is_array($tmp)) {
        $selectedSpec = array_map('strval', $tmp);
    } else {
        $selectedSpec = array_filter(array_map('trim', explode(',', (string) $emp['spesialisasi'])));
    }
}
// Util foto
$fotoSrc = '';
if (!empty($emp['foto']) && is_file(FCPATH . 'images/karyawan/' . $emp['foto'])) {
    $fotoSrc = base_url('images/karyawan/' . $emp['foto']);
}
?>

<main class="employee-form-page">
    <header class="ef-head">
        <h2><i class="fa-solid fa-user-gear"></i> Edit Profil Admin</h2>
    </header>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="card" style="border-left:4px solid #10B981;"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="card" style="border-left:4px solid #EF4444;"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form action="<?= site_url('admin/profile/update') ?>" method="post" enctype="multipart/form-data" class="ef-form"
        id="profileForm">
        <?= csrf_field() ?>

        <section class="card ef-card">
            <div class="ef-grid">
                <!-- Kolom kiri -->
                <div class="ef-panel">
                    <div class="field">
                        <label>Nama</label>
                        <input type="text" name="nama" class="input" value="<?= esc($emp['nama'] ?? '') ?>" required>
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" class="input" value="<?= esc($emp['email'] ?? '') ?>">
                    </div>

                    <div class="field">
                        <label>No. Telepon</label>
                        <input type="text" name="no_telepon" class="input" value="<?= esc($emp['no_telepon'] ?? '') ?>">
                    </div>

                    <div class="field">
                        <label>Jabatan</label>
                        <input type="text" name="jabatan" class="input" value="<?= esc($emp['jabatan'] ?? '') ?>">
                    </div>

                    <!-- Spesialisasi -->
                    <div class="field">
                        <label class="mb-2 block">Spesialisasi</label>

                        <!-- PPAT -->
                        <div class="mb-3 border rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <strong>PPAT</strong>
                                <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" id="checkAllPPAT">
                                    <span>Pilih semua PPAT</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <?php foreach ($PPAT_OPTIONS as $opt):
                                    $id = 'sp_ppat_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($opt));
                                    $checked = in_array($opt, $selectedSpec, true) ? 'checked' : '';
                                    ?>
                                    <label for="<?= $id ?>" class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" class="ck-ppat" id="<?= $id ?>" name="spesialisasi[]"
                                            value="<?= esc($opt) ?>" <?= $checked ?>>
                                        <span><?= esc($opt) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Notaris -->
                        <div class="mb-3 border rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <strong>Notaris</strong>
                                <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" id="checkAllNotaris">
                                    <span>Pilih semua Notaris</span>
                                </label>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <?php foreach ($NOTARIS_OPTIONS as $opt):
                                    $id = 'sp_notaris_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($opt));
                                    $checked = in_array($opt, $selectedSpec, true) ? 'checked' : '';
                                    ?>
                                    <label for="<?= $id ?>" class="inline-flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" class="ck-notaris" id="<?= $id ?>" name="spesialisasi[]"
                                            value="<?= esc($opt) ?>" <?= $checked ?>>
                                        <span><?= esc($opt) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <p class="hint text-sm text-gray-600">Boleh pilih berapa pun, minimal 1 spesialisasi dianjurkan.
                        </p>
                    </div>

                    <div class="field">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" rows="5" class="textarea"
                            placeholder="Tuliskan bio singkat..."><?= esc($emp['deskripsi'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Kolom kanan -->
                <div class="ef-panel">
                    <div class="field">
                        <label>Foto (jpg/png)</label>
                        <input type="file" name="foto" accept="image/*" class="file" id="fotoInput">
                        <div class="ef-photo">
                            <img id="fotoPreview" src="<?= esc($fotoSrc) ?>"
                                style="<?= $fotoSrc ? '' : 'display:none' ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label>Status</label>
                        <?php $st = strtolower((string) ($emp['status'] ?? 'aktif')); ?>
                        <select name="status" class="select">
                            <option value="aktif" <?= $st === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $st === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section class="ef-actions">
            <a href="<?= site_url('admin') ?>" class="btn">Kembali</a>
            <button type="submit" class="btn btn--primary">Simpan</button>
        </section>
    </form>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Preview foto
        const input = document.getElementById('fotoInput');
        const preview = document.getElementById('fotoPreview');
        input?.addEventListener('change', e => {
            const f = e.target.files?.[0];
            if (!f) return;
            const r = new FileReader();
            r.onload = ev => { preview.src = ev.target.result; preview.style.display = 'block'; };
            r.readAsDataURL(f);
        });

        // Master checkbox "pilih semua" per kelompok
        function initGroup(masterId, itemSelector) {
            const master = document.getElementById(masterId);
            const items = Array.from(document.querySelectorAll(itemSelector));

            function syncMaster() {
                if (!master) return;
                if (!items.length) { master.checked = false; return; }
                master.checked = items.every(cb => cb.checked);
                master.indeterminate = !master.checked && items.some(cb => cb.checked);
            }
            master?.addEventListener('change', () => {
                items.forEach(cb => cb.checked = master.checked);
                syncMaster();
            });
            items.forEach(cb => cb.addEventListener('change', syncMaster));
            syncMaster();
        }
        initGroup('checkAllPPAT', '.ck-ppat');
        initGroup('checkAllNotaris', '.ck-notaris');

        // (Opsional) Validasi minimal 1 spesialisasi
        document.getElementById('profileForm')?.addEventListener('submit', (e) => {
            const any = document.querySelectorAll('input[name="spesialisasi[]"]:checked').length > 0;
            if (!any) {
                if (!confirm('Tidak ada spesialisasi dipilih. Lanjutkan simpan?')) e.preventDefault();
            }
        });
    });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>