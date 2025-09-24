<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$rows = $rows ?? [];
$q = trim((string) ($q ?? ''));
$csrfName = $csrfName ?? csrf_token();
$csrfHash = $csrfHash ?? csrf_hash();
?>

<style>
    /* biar konsisten lebar “dashboard” */
    .container-wide {
        max-width: 1600px;
    }

    /* ~ > 7xl */
</style>

<section class="container-wide mx-auto px-6 lg:px-10 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl md:text-[28px] font-extrabold text-gray-900">Kelola Pekerjaan (Beranda)</h1>
            <p class="text-sm text-gray-500">Kartu layanan yang tampil di halaman depan.</p>
        </div>
        <a href="<?= site_url('admin/pekerjaan/create') ?>"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-amber-600 text-white hover:bg-amber-700 shadow">
            <i class="fa-solid fa-circle-plus"></i> Tambah Pekerjaan
        </a>
    </div>

    <!-- Search bar -->
    <form class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mb-5" method="get" action="">
        <input type="text" name="q" value="<?= esc($q) ?>" placeholder="Cari judul, slug, kategori…"
            class="w-full sm:w-[460px] px-4 py-2.5 rounded-lg border">
        <div class="flex items-center gap-2">
            <button class="px-4 py-2.5 rounded-lg bg-gray-800 text-white">Cari</button>
            <a href="<?= site_url('admin/pekerjaan') ?>" class="px-4 py-2.5 rounded-lg border">Reset</a>
        </div>
    </form>

    <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-gray-50 font-semibold text-gray-800">Daftar Pekerjaan</div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-[13px] md:text-sm">
                <colgroup>
                    <col style="width:5rem">
                    <col style="width:40%">
                    <col style="width:12%">
                    <col style="width:28%">
                    <col style="width:12%">
                    <col style="width:16%">
                </colgroup>
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Judul</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-left">Icon</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($rows as $i => $r): ?>
                        <?php
                        $badgeCat = $r['category'] === 'PPAT' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700';
                        $badgeSt = (int) $r['is_active'] === 1 ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600';
                        ?>
                        <tr class="border-b last:border-0">
                            <td class="px-4 py-4 align-top text-gray-700"><?= $i + 1 ?></td>

                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-gray-900"><?= esc($r['title']) ?></div>
                                <div class="text-xs text-gray-500 mt-0.5">slug: <?= esc($r['slug']) ?></div>
                                <?php if (!empty($r['excerpt'])): ?>
                                    <div class="text-xs text-gray-500 mt-1 line-clamp-2"><?= esc($r['excerpt']) ?></div>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full <?= $badgeCat ?>">
                                    <?= esc($r['category']) ?>
                                </span>
                            </td>

                            <td class="px-4 py-4 align-top">
                                <div class="inline-flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-lg bg-amber-100 grid place-items-center text-amber-700">
                                        <i class="<?= esc($r['icon'] ?: 'fa-solid fa-file-lines') ?>"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-gray-800 text-[13px]"><?= esc($r['icon'] ?: '—') ?></div>
                                        <div class="text-xs text-gray-500">Font Awesome class</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4 align-top">
                                <span class="inline-flex px-2.5 py-1 text-xs rounded-full <?= $badgeSt ?>">
                                    <?= (int) $r['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>

                            <td class="px-4 py-4 align-top">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="<?= site_url('admin/pekerjaan/edit/' . $r['id']) ?>"
                                        class="px-3 py-1.5 rounded-lg border text-xs">Edit</a>
                                    <form class="inline" method="post"
                                        action="<?= site_url('admin/pekerjaan/delete/' . $r['id']) ?>"
                                        onsubmit="return confirm('Hapus data ini?')">
                                        <input type="hidden" name="<?= esc($csrfName) ?>" value="<?= esc($csrfHash) ?>">
                                        <button class="px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>