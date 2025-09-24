<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$row = $row ?? [];
$icons = $icons ?? [];
$isEdit = !empty($row);
$action = $isEdit ? site_url('admin/pekerjaan/update/' . $row['id']) : site_url('admin/pekerjaan/store');

$cat = old('category', $row['category'] ?? 'PPAT');
$selIcon = old('icon', $row['icon'] ?? 'fa-solid fa-file-lines');
$isActive = (int) old('is_active', $row['is_active'] ?? 1);
?>

<style>
    .container-wide {
        max-width: 1600px;
    }

    /* bikin lebih lega seperti halaman lain */
</style>

<section class="container-wide mx-auto px-6 lg:px-10 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl md:text-[28px] font-extrabold text-gray-900">
            <?= esc($title ?? ($isEdit ? 'Edit Pekerjaan' : 'Tambah Pekerjaan')) ?>
        </h1>
        <a href="<?= site_url('admin/pekerjaan') ?>" class="px-4 py-2.5 rounded-lg border text-sm">Kembali</a>
    </div>

    <?php if (session('err')): ?>
        <div class="mb-5 p-3 rounded-xl bg-red-100 text-red-800 text-sm"><?= session('err') ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $action ?>" autocomplete="off">
        <?= csrf_field() ?>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- KIRI -->
            <div class="bg-white border rounded-2xl p-6 shadow-sm space-y-5">
                <div>
                    <label class="text-sm text-gray-700">Kategori</label>
                    <select name="category" class="w-full border rounded-lg px-3 py-2">
                        <option value="PPAT" <?= $cat === 'PPAT' ? 'selected' : '' ?>>PPAT</option>
                        <option value="NOTARIS" <?= $cat === 'NOTARIS' ? 'selected' : '' ?>>NOTARIS</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm text-gray-700">Judul</label>
                    <input type="text" name="title" value="<?= esc(old('title', $row['title'] ?? '')) ?>"
                        class="w-full border rounded-lg px-3 py-2" required>
                    <p class="text-xs text-gray-500 mt-1">Slug akan dibuat otomatis dari judul.</p>
                </div>

                <div>
                    <label class="text-sm text-gray-700">Ringkasan (excerpt)</label>
                    <input type="text" name="excerpt"
                        value="<?= esc(old('excerpt', $row['excerpt'] ?? 'Klik untuk melihat ketersediaan jadwal.')) ?>"
                        class="w-full border rounded-lg px-3 py-2" maxlength="255">
                    <p class="text-xs text-gray-500 mt-1">Teks pendek yang tampil di kartu beranda.</p>
                </div>

                <div>
                    <label class="text-sm text-gray-700">Deskripsi (opsional)</label>
                    <textarea name="description" rows="6"
                        class="w-full border rounded-lg px-3 py-2"><?= esc(old('description', $row['description'] ?? '')) ?></textarea>
                </div>
            </div>

            <!-- KANAN -->
            <div class="bg-white border rounded-2xl p-6 shadow-sm space-y-5">
                <div>
                    <label class="text-sm text-gray-700">Icon</label>
                    <select name="icon" id="iconSel" class="w-full border rounded-lg px-3 py-2">
                        <?php foreach ($icons as $cls => $label): ?>
                            <option value="<?= esc($cls) ?>" <?= $selIcon === $cls ? 'selected' : '' ?>>
                                <?= esc($label) ?> (<?= esc($cls) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="mt-3 flex items-center gap-3">
                        <span class="w-10 h-10 rounded-lg bg-amber-100 grid place-items-center text-amber-700">
                            <i id="iconPreview" class="<?= esc($selIcon) ?>"></i>
                        </span>
                        <div class="text-xs text-gray-500">
                            Simpan full class Font Awesome, misal: <code>fa-solid fa-location-dot</code>.
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-700">Status</label>
                        <select name="is_active" class="w-full border rounded-lg px-3 py-2">
                            <option value="1" <?= $isActive === 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= $isActive === 0 ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-start gap-2">
            <a href="<?= site_url('admin/pekerjaan') ?>" class="px-4 py-2.5 rounded-lg border">Batal</a>
            <button class="px-4 py-2.5 rounded-lg bg-amber-600 text-white hover:bg-amber-700">Simpan</button>
        </div>
    </form>
</section>

<script>
    const sel = document.getElementById('iconSel');
    const prev = document.getElementById('iconPreview');
    if (sel && prev) sel.addEventListener('change', () => { prev.className = sel.value; });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>