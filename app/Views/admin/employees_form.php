<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$mode = ($mode ?? 'create');
$isEdit = ($mode === 'edit');
$r = $row ?? [];

// Daftar opsi (silakan sesuaikan)
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

// Preselect untuk edit: dukung JSON atau CSV
$selectedSpec = [];
if (!empty($r['spesialisasi'])) {
    $tmp = json_decode((string) $r['spesialisasi'], true);
    if (is_array($tmp)) {
        $selectedSpec = array_map('strval', $tmp);
    } else {
        $selectedSpec = array_filter(array_map('trim', explode(',', (string) $r['spesialisasi'])));
    }
}
?>

<main class="employee-form-page">
    <header class="ef-head">
        <h2><i class="fa-solid fa-id-card-clip"></i> <?= $isEdit ? 'Edit' : 'Tambah' ?> Karyawan</h2>
    </header>

    <form
        action="<?= $isEdit ? site_url('admin/employees/update/' . (int) ($r['id'] ?? 0)) : site_url('admin/employees/store') ?>"
        method="post" enctype="multipart/form-data" class="ef-form" id="employeeForm">
        <?= csrf_field() ?>

        <section class="card ef-card">
            <div class="ef-grid">
                <!-- Kolom kiri -->
                <div class="ef-panel">
                    <div class="field">
                        <label>Nama</label>
                        <input name="nama" value="<?= esc($r['nama'] ?? '') ?>" class="input">
                    </div>

                    <div class="field">
                        <label>Email</label>
                        <input name="email" value="<?= esc($r['email'] ?? '') ?>" class="input">
                    </div>

                    <div class="field">
                        <label>Jabatan</label>
                        <input name="jabatan" value="<?= esc($r['jabatan'] ?? '') ?>" class="input">
                    </div>

                    <!-- Spesialisasi: checkbox multi-pilih + "pilih semua" per kelompok -->
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

                        <!-- Pesan kecil -->
                        <p class="hint text-sm text-gray-600">Boleh pilih berapa pun, tetapi minimal 1 spesialisasi
                            harus dipilih.</p>
                    </div>

                    <div class="field">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" rows="5"
                            class="textarea"><?= esc($r['deskripsi'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Kolom kanan -->
                <div class="ef-panel">
                    <div class="field">
                        <label>Foto (jpg/png)</label>
                        <input type="file" name="foto" accept="image/*" class="file" id="fotoInput">
                        <div class="ef-photo">
                            <img id="fotoPreview"
                                src="<?= !empty($r['foto']) ? base_url('images/karyawan/' . $r['foto']) : '' ?>"
                                style="<?= empty($r['foto']) ? 'display:none' : '' ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label>Status</label>
                        <select name="status" class="select">
                            <?php $st = strtolower((string) ($r['status'] ?? 'aktif')); ?>
                            <option value="aktif" <?= $st === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $st === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section class="ef-actions">
            <a href="<?= site_url('admin/employees') ?>" class="btn">Batal</a>
            <button class="btn btn--primary"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan' ?></button>
        </section>
    </form>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Preview foto
        const input = document.getElementById('fotoInput');
        const preview = document.getElementById('fotoPreview');
        if (input) {
            input.addEventListener('change', e => {
                const f = e.target.files[0];
                if (!f) return;
                const r = new FileReader();
                r.onload = ev => { preview.src = ev.target.result; preview.style.display = 'block'; };
                r.readAsDataURL(f);
            });
        }

        // Helper: sinkron master checkbox (pilih semua)
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

        // Validasi minimal 1 spesialisasi
        const form = document.getElementById('employeeForm');
        form?.addEventListener('submit', (e) => {
            const anyChecked = document.querySelectorAll('input[name="spesialisasi[]"]:checked').length > 0;
            if (!anyChecked) {
                e.preventDefault();
                alert('Pilih minimal 1 spesialisasi.');
                const first = document.querySelector('input[name="spesialisasi[]"]');
                first?.focus();
            }
        });
    });
</script>

<?= $this->endSection() ?>