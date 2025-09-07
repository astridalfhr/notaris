<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('employee'); // specs_to_string() ?>

<?php
// ==== View vars aman ====
$employees = $employees ?? [];
$date = $date ?? date('Y-m-d');
$activeService = $activeService ?? null;
$serviceSlug = $serviceSlug ?? '';
$isAdmin = $isAdmin ?? (strtolower((string) (session('role') ?? '')) === 'admin');

// dari Dashboard::index
$bookingsToday = $bookingsToday ?? [];

// Base URLs untuk JS
$jadwalBase = rtrim(site_url('jadwal'), '/');
$bookingCreate = site_url('booking/create');
$todayPreset = (string) $date;

// Role saat ini (fallback: kalau $isAdmin true -> admin, else user)
$currentRole = strtolower((string) (session('role') ?? ($isAdmin ? 'admin' : 'user')));

// normalizer STATUS → untuk badge/css
$normalize = static function (?string $s): string {
    $s = strtolower(trim((string) $s));

    $confirmed = ['approve', 'approved', 'confirm', 'confirmed', 'accept', 'accepted', 'setuju', 'disetujui', 'terima'];
    $cancelled = ['cancel', 'cancelled', 'canceled', 'reject', 'rejected', 'decline', 'declined', 'denied', 'batal', 'dibatalkan', 'ditolak', 'void'];
    $completed = ['done', 'finish', 'finished', 'complete', 'completed', 'selesai'];
    $pending = ['pending', 'booked', 'menunggu', 'requested', 'request'];

    if (in_array($s, $confirmed, true))
        return 'confirmed';
    if (in_array($s, $cancelled, true))
        return 'cancelled';
    if (in_array($s, $completed, true))
        return 'completed';
    if (in_array($s, $pending, true))
        return 'pending';
    return ($s ?: 'pending');
};
?>
<style>
    :root {
        --navTop: 64px;
    }

    .panelBelowNav {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        top: var(--navTop);
    }
</style>

<section class="py-10 bg-gray-100">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-end justify-between mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900">Jadwal Layanan Notaris &amp; PPAT</h1>
                <?php if ($activeService): ?>
                    <div class="mt-2 text-sm text-gray-700">
                        Filter:
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border bg-white">
                            <span
                                class="text-xs uppercase tracking-wide text-gray-500"><?= esc($activeService['category']) ?></span>
                            <strong class="text-gray-800"><?= esc($activeService['name']) ?></strong>
                        </span>
                        <a class="ml-2 text-blue-700 hover:underline"
                            href="<?= site_url('layanan?date=' . rawurlencode($date ?? date('Y-m-d'))) ?>">Reset</a>
                    </div>
                <?php else: ?>
                    <p class="mt-2 text-sm text-gray-600">Pilih karyawan untuk melihat jadwal. Anda bisa mengganti tanggal
                        di panel jadwal.</p>
                <?php endif; ?>
            </div>

            <?php if ($isAdmin): ?>
                <div class="flex items-center gap-2">
                    <button id="btnAdminPanel" type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm shadow">
                        <i class="fa-solid fa-screwdriver-wrench"></i> Arsip Surat
                    </button>
                    <button id="btnWorkPanel" type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm shadow">
                        <i class="fa-solid fa-briefcase"></i> Halaman Kerja
                    </button>
                    <a href="<?= site_url('admin/slot') ?>"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm shadow">
                        <i class="fa-solid fa-calendar-check"></i> Kelola Slot
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($isAdmin): ?>
            <!-- ========== ADMIN: JADWAL HARI INI (SERVER-SIDE) ========== -->
            <div class="mb-8">
                <div class="rounded-2xl border bg-white shadow-sm">
                    <div class="px-4 py-3 border-b bg-amber-50/60 rounded-t-2xl flex items-center justify-between">
                        <h3 class="font-semibold text-amber-700 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check"></i> Jadwal Hari Ini
                        </h3>
                        <div class="text-xs text-gray-500"><?= esc(date('l, d F Y')) ?></div>
                    </div>

                    <div class="overflow-x-auto pb-4">
                        <table class="min-w-full text-sm table-auto">
                            <colgroup>
                                <col style="width:5rem">
                                <col style="width:40%">
                                <col style="width:16%">
                                <col style="width:14%">
                                <col style="width:12%">
                                <col style="width:18%">
                            </colgroup>
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left">No</th>
                                    <th class="px-3 py-2 text-left">Pengguna</th>
                                    <th class="px-3 py-2 text-left">Tanggal</th>
                                    <th class="px-3 py-2 text-left">Jam</th>
                                    <th class="px-3 py-2 text-left">Status</th>
                                    <th class="px-3 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bookingsToday)): ?>
                                    <?php $i = 1;
                                    foreach ($bookingsToday as $b):
                                        $status = $normalize($b['status'] ?? '');
                                        $tgl = !empty($b['tanggal']) ? date('Y-m-d', strtotime($b['tanggal'])) : '-';
                                        $jam = trim((string) ($b['jam'] ?? '')) ?: '-';
                                        $bid = (int) ($b['id'] ?? 0);
                                        $slotId = (int) ($b['slot_id'] ?? $b['jadwal_id'] ?? 0);
                                        $badgeClass = match ($status) {
                                            'confirmed' => 'bg-green-100 text-green-700',
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'completed' => 'bg-blue-100 text-blue-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                        ?>
                                        <tr class="border-b last:border-0">
                                            <td class="px-3 py-2"><?= $i++ ?></td>
                                            <td class="px-3 py-2 min-w-0">
                                                <div class="truncate font-medium"><?= esc($b['user_nama'] ?? '-') ?></div>
                                                <div class="text-xs text-gray-500 truncate"><?= esc($b['user_email'] ?? '-') ?>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap"><?= esc($tgl) ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap"><?= esc($jam) ?></td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs <?= $badgeClass ?>"><?= ucfirst($status) ?></span>
                                            </td>
                                            <td class="px-3 py-2 text-center whitespace-nowrap">
                                                <div class="inline-flex items-center gap-1">
                                                    <a href="<?= site_url('booking/detail/' . $bid . '?back=' . rawurlencode(site_url('layanan'))) ?>"
                                                        class="px-2.5 py-1 rounded bg-slate-600 hover:bg-slate-700 text-white text-xs">Detail</a>

                                                    <?php if ($status === 'pending'): ?>
                                                        <form action="<?= site_url('admin/approve/' . $bid) ?>" method="post"
                                                            class="inline">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="back" value="layanan">
                                                            <button
                                                                class="px-2.5 py-1 rounded bg-green-600 hover:bg-green-700 text-white text-xs"
                                                                type="submit">Confirm</button>
                                                        </form>
                                                        <form action="<?= site_url('admin/reject/' . $bid) ?>" method="post"
                                                            class="inline" onsubmit="return confirm('Tolak booking ini?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="back" value="layanan">
                                                            <button
                                                                class="px-2.5 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs"
                                                                type="submit">Cancel</button>
                                                        </form>

                                                    <?php elseif ($status === 'confirmed'): ?>
                                                        <?php if ($slotId > 0): ?>
                                                            <form action="<?= site_url('admin/slot/complete/' . $slotId) ?>" method="post"
                                                                class="inline" onsubmit="return confirm('Tandai sebagai selesai?');">
                                                                <?= csrf_field() ?>
                                                                <input type="hidden" name="back" value="layanan">
                                                                <button
                                                                    class="px-2.5 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs"
                                                                    type="submit">Selesai</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-xs text-gray-400 italic">Tanpa slot</span>
                                                        <?php endif; ?>
                                                        <form action="<?= site_url('admin/reject/' . $bid) ?>" method="post"
                                                            class="inline" onsubmit="return confirm('Batalkan booking ini?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="back" value="layanan">
                                                            <button
                                                                class="px-2.5 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs"
                                                                type="submit">Cancel</button>
                                                        </form>

                                                    <?php else: ?>
                                                        <span class="text-xs text-gray-400">-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-3 py-4 text-gray-500">Tidak ada booking untuk hari ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($employees)): ?>
            <div class="rounded-lg border border-dashed bg-white p-10 text-center text-gray-600">Belum ada karyawan untuk
                layanan ini.</div>
        <?php else: ?>
            <div id="employee-list"
                class="grid lg:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-6 transition-all duration-300">
                <?php foreach ($employees as $emp): ?>
                    <?php
                    $foto = (string) ($emp['foto'] ?? '');
                    $src = $foto !== '' ? base_url('images/karyawan/' . $foto) : 'https://via.placeholder.com/96?text=IMG';
                    $specText = specs_to_string($emp['spesialisasi'] ?? '');
                    ?>
                    <div class="employee-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-500">
                        <img src="<?= esc($src) ?>" alt="<?= esc($emp['nama'] ?? 'Karyawan') ?>"
                            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover" />
                        <h3 class="text-lg font-semibold text-center"><?= esc($emp['nama'] ?? '—') ?></h3>
                        <p class="text-sm text-gray-600 text-center"><?= esc($emp['jabatan'] ?? '-') ?></p>
                        <?php if ($specText !== ''): ?>
                            <p class="text-xs text-gray-400 text-center italic mt-1">Spesialis: <?= esc($specText) ?></p>
                        <?php endif; ?>
                        <button onclick="openSchedule(<?= (int) ($emp['id'] ?? 0) ?>)"
                            class="mt-4 block w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl transition-all">Lihat
                            Jadwal</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- =================== PANELS & MODALS (TETAP ADA) =================== -->

<section class="container mx-auto px-6 py-12">
    <!-- Schedule panel -->
    <div id="schedule-panel" class="hidden panelBelowNav bg-white z-30 p-6 overflow-auto transition-all">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-gray-800">Jadwal
                Konsultasi<?= $activeService ? ' — ' . esc($activeService['name']) : '' ?></h3>
            <button type="button" onclick="closeSchedule()"
                class="text-red-500 hover:text-red-700 text-sm font-semibold">Tutup ✖</button>
        </div>

        <div class="flex items-center gap-2 mb-4">
            <button id="btnPrev" class="px-3 py-2 rounded-lg border hover:bg-gray-50" type="button">←</button>
            <input id="datePicker" type="date" class="px-3 py-2 rounded-lg border" value="<?= esc($todayPreset) ?>">
            <button id="btnNext" class="px-3 py-2 rounded-lg border hover:bg-gray-50" type="button">→</button>
            <span id="dateNote" class="ml-3 text-sm text-gray-500"></span>
        </div>

        <div id="schedule-content" class="space-y-4"></div>
    </div>

    <?php if ($isAdmin): ?>
        <!-- Work panel -->
        <div id="work-panel" class="hidden panelBelowNav z-40 bg-white p-6 overflow-auto">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <button id="btnWorkBack" class="px-3 py-2 rounded-lg border hover:bg-gray-50" type="button">←
                        Kembali</button>
                    <h3 class="text-2xl font-bold text-gray-800">Halaman Kerja</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btnAddWork" type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm shadow">
                        <i class="fa-solid fa-file-circle-plus"></i> Tambah File
                    </button>
                </div>
            </div>

            <div class="space-y-10">
                <section>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xl font-semibold">PPAT</h4>
                    </div>
                    <div id="work-ppat"></div>
                </section>

                <section>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xl font-semibold">Notaris</h4>
                    </div>
                    <div id="work-notaris"></div>
                </section>
            </div>
        </div>

        <!-- Work Modal -->
        <div id="work-modal" class="hidden fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative bg-white max-w-2xl w-11/12 mx-auto mt-16 rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h5 id="workModalTitle" class="text-lg font-bold">Tambah File</h5>
                    <button id="workModalClose" type="button"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200">✕</button>
                </div>

                <form id="workForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="workId">

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-700">Kategori</label>
                            <select name="category" id="workCategory" class="w-full border rounded-lg px-3 py-2">
                                <option value="PPAT">PPAT</option>
                                <option value="Notaris">Notaris</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-700">Sub-jenis</label>
                            <select name="subtype" id="workSubtype" class="w-full border rounded-lg px-3 py-2"></select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm text-gray-700">Judul</label>
                            <input type="text" name="title" id="workTitle" class="w-full border rounded-lg px-3 py-2"
                                placeholder="Judul dokumen (opsional)">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm text-gray-700">Catatan</label>
                            <textarea name="notes" id="workNotes" rows="3" class="w-full border rounded-lg px-3 py-2"
                                placeholder="Catatan (opsional)"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm text-gray-700">File</label>
                            <input type="file" name="file" id="workFile" class="block w-full text-sm">
                            <p class="text-xs text-gray-500 mt-1">PDF, gambar, dokumen umum diperbolehkan.</p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" id="workCancel" class="px-3 py-2 rounded-lg border">Batal</button>
                        <button type="submit" id="workSubmit"
                            class="px-3 py-2 rounded-lg bg-blue-600 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Admin panel (overlay) — ARSIP -->
        <div id="admin-panel" class="hidden panelBelowNav z-40 bg-white overflow-auto">
            <div class="bg-amber-50 border-b border-amber-200 px-6 py-4 flex items-center justify-between sticky top-0">
                <div class="text-lg font-semibold">Panel Administrasi</div>
                <button id="btnAdminClose" class="px-3 py-2 rounded-lg border hover:bg-amber-100">Tutup ✖</button>
            </div>

            <div class="px-6 py-6 space-y-8">
                <!-- Arsip surat -->
                <section>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xl font-semibold">Arsip Surat Menyurat</h3>
                        <div class="flex items-center gap-2">
                            <input type="month" id="arsipMonth" class="border rounded-lg px-3 py-2 text-sm"
                                value="<?= esc(date('Y-m')) ?>">
                            <a id="btnArsipReport" class="px-3 py-2 rounded-lg bg-amber-600 text-white text-sm"
                                href="<?= site_url('admin/arsip/report?month=' . date('Y-m')) ?>">
                                <i class="fa-solid fa-file-pdf"></i> Download Laporan Bulanan
                            </a>
                            <button id="btnArsipAdd" class="px-3 py-2 rounded-lg border bg-white text-sm">
                                <i class="fa-solid fa-file-circle-plus"></i> Upload Surat
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="rounded-xl border bg-white">
                            <div class="px-4 py-3 bg-gray-50 font-semibold">Surat Masuk</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 w-14">No</th>
                                            <th class="px-3 py-2">Tanggal</th>
                                            <th class="px-3 py-2">Nomor Surat</th>
                                            <th class="px-3 py-2">Perihal</th>
                                            <th class="px-3 py-2">Pengirim</th>
                                            <th class="px-3 py-2 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="arsipMasukBody">
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-gray-500">Memuat…</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="rounded-xl border bg-white">
                            <div class="px-4 py-3 bg-gray-50 font-semibold">Surat Keluar</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 w-14">No</th>
                                            <th class="px-3 py-2">Tanggal</th>
                                            <th class="px-3 py-2">Nomor Surat</th>
                                            <th class="px-3 py-2">Perihal</th>
                                            <th class="px-3 py-2">Penerima</th>
                                            <th class="px-3 py-2 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="arsipKeluarBody">
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-gray-500">Memuat…</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Arsip Upload/Edit Modal -->
        <div id="arsipModal" class="hidden fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative bg-white max-w-2xl w-11/12 mx-auto mt-16 rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-lg font-bold" id="arsipModalTitle">Upload Surat</h5>
                    <button type="button" id="arsipClose"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200">✕</button>
                </div>
                <form id="arsipForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="grid md:grid-cols-2 gap-4" id="arsipGrid">
                        <div>
                            <label class="text-sm">Jenis</label>
                            <select name="jenis" class="w-full border rounded-lg px-3 py-2">
                                <option value="masuk">Masuk</option>
                                <option value="keluar">Keluar</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm">Tanggal</label>
                            <input type="date" name="tanggal" class="w-full border rounded-lg px-3 py-2"
                                value="<?= esc(date('Y-m-d')) ?>">
                        </div>
                        <div>
                            <label class="text-sm">Nomor Surat</label>
                            <input type="text" name="nomor_surat" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm">Pihak (Pengirim/Penerima)</label>
                            <input type="text" name="pihak" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm">Perihal</label>
                            <input type="text" name="perihal" class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm">File</label>
                            <input type="file" name="file" class="block w-full text-sm" required>
                            <p class="text-xs text-gray-500 mt-1" id="arsipFileHint">PDF/gambar/dokumen maks 10MB.</p>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" id="arsipCancel" class="px-3 py-2 rounded-lg border">Batal</button>
                        <button type="submit" id="arsipSubmit"
                            class="px-3 py-2 rounded-lg bg-amber-600 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- Preview Modal (PDF/Gambar/Office) -->
<div id="filePreviewModalTW" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative mx-auto mt-14 w-11/12 max-w-5xl bg-white rounded-xl shadow">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h4 class="font-semibold text-lg" id="pmTitleTW">Pratinjau Berkas</h4>
            <button type="button" class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200" data-pm-close>✕</button>
        </div>
        <div class="p-0" id="pmBodyTW" style="height:70vh; overflow:hidden;"></div>
        <div class="flex items-center justify-between gap-2 px-4 py-3 border-t">
            <div class="text-xs text-gray-500">Gunakan tombol di kanan untuk membuka/unduh.</div>
            <div class="flex items-center gap-2">
                <a id="pmOpenTW" class="px-3 py-2 rounded bg-amber-500 hover:bg-amber-600 text-white text-sm"
                    target="_blank" rel="noopener">
                    <i class="fa-solid fa-up-right-from-square"></i> Lihat di Tab Baru
                </a>
                <a id="pmDownloadTW" class="px-3 py-2 rounded bg-white border hover:bg-gray-50 text-sm" download>
                    <i class="fa-solid fa-download"></i> Unduh
                </a>
                <button type="button" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white text-sm"
                    data-pm-close>
                    <i class="fa-solid fa-xmark"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // ===== util tanggal =====
    const tzOffsetFix = (d) => { const p = n => String(n).padStart(2, '0'); return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`; };
    const addDays = (dateStr, n) => { const d = new Date((dateStr || '<?= $todayPreset ?>') + 'T00:00:00'); d.setDate(d.getDate() + n); return tzOffsetFix(d); };

    // ===== role & guard booking =====
    const ROLE = "<?= esc($currentRole) ?>";
    const CAN_BOOK = ROLE === 'user';

    // ===== panel jadwal =====
    let currentEmployeeId = null;
    const baseJadwal = "<?= $jadwalBase ?>";
    const bookingCreate = "<?= $bookingCreate ?>";

    function openSchedule(employeeId) {
        currentEmployeeId = employeeId;
        document.getElementById("employee-list")?.classList.add("blur-sm", "pointer-events-none");
        document.getElementById("schedule-panel")?.classList.remove("hidden");
        const dn = document.getElementById('dateNote'); if (dn) dn.textContent = '';
        loadSchedule();
    }
    function closeSchedule() {
        document.getElementById("schedule-panel")?.classList.add("hidden");
        document.getElementById("employee-list")?.classList.remove("blur-sm", "pointer-events-none");
        const box = document.getElementById("schedule-content"); if (box) box.innerHTML = '';
        currentEmployeeId = null;
    }

    async function loadSchedule() {
        if (!currentEmployeeId) return;
        const datePicker = document.getElementById('datePicker');
        const selectedDate = (datePicker?.value || "<?= $todayPreset ?>");
        const dateNote = document.getElementById('dateNote'); if (dateNote) dateNote.textContent = `Menampilkan jadwal tanggal ${selectedDate}`;

        try {
            const url = `${baseJadwal}/${encodeURIComponent(currentEmployeeId)}?date=${encodeURIComponent(selectedDate)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const all = await res.json();

            const rows = (Array.isArray(all) ? all : []).filter(j => (j.tanggal || '').slice(0, 10) === selectedDate);
            const box = document.getElementById("schedule-content");
            if (!box) return;
            box.innerHTML = '';

            if (!rows.length) {
                box.innerHTML = `<div class="border p-4 rounded-lg bg-gray-50 text-gray-600">Belum ada slot untuk tanggal ini.</div>`;
                return;
            }

            rows.forEach(j => {
                const isAvail = String(j.status || '').toLowerCase() === 'available';
                const statusColor = isAvail ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                const jam = (j.jam || j.start_time || '').toString();
                const note = (j.note || j.keterangan || '');
                const link = `<?= $bookingCreate ?>?karyawan=${encodeURIComponent(currentEmployeeId)}&jadwal=${encodeURIComponent(j.id)}`;
                const action = isAvail
                    ? `<a href="${link}" data-booking-link="1" class="mt-2 inline-block bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">Booking</a>`
                    : `<span class="mt-2 inline-block bg-gray-300 text-gray-600 px-4 py-1 rounded text-sm cursor-not-allowed">Booked</span>`;

                box.insertAdjacentHTML('beforeend', `
        <div class="border p-4 rounded-lg shadow bg-white flex items-center justify-between gap-4">
          <div class="min-w-0">
            <div class="text-sm text-gray-500 whitespace-nowrap">Tanggal / Hari: ${j.tanggal || '-'}</div>
            <div class="font-medium text-gray-800 whitespace-nowrap">Jam: ${jam || '-'}</div>
            <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs ${statusColor}">${j.status || 'unknown'}</span>
            ${note ? `<div class="text-xs text-gray-500 mt-1"><i class="fa-regular fa-circle-question"></i> ${note}</div>` : ``}
          </div>
          <div class="shrink-0">${action}</div>
        </div>`);
            });
        } catch (e) {
            const box = document.getElementById("schedule-content");
            if (box) box.innerHTML = `<div class="border p-4 rounded-lg bg-red-50 text-red-700">Gagal memuat jadwal. Coba lagi.</div>`;
        }
    }

    document.getElementById('btnPrev')?.addEventListener('click', () => { const dp = document.getElementById('datePicker'); if (!dp) return; dp.value = addDays(dp.value, -1); loadSchedule(); });
    document.getElementById('btnNext')?.addEventListener('click', () => { const dp = document.getElementById('datePicker'); if (!dp) return; dp.value = addDays(dp.value, 1); loadSchedule(); });
    document.getElementById('datePicker')?.addEventListener('change', loadSchedule);

    // Guard: non-user dilarang klik booking
    document.addEventListener('click', function (e) {
        const a = e.target.closest('a[data-booking-link]');
        if (a && !CAN_BOOK) {
            e.preventDefault();
            alert('Hanya akun user yang diperbolehkan melakukan booking.\nAdmin tidak diperbolehkan membuat booking.');
        }
    });

    <?php if ($isAdmin): ?>
            // ===== WORK PANEL (Halaman Kerja) =====
            (() => {
                const FEED_URL = "<?= site_url('admin/kerja/feed') ?>";
                const UPLOAD_URL = "<?= site_url('admin/kerja/upload') ?>";
                const UPDATE_URL = id => "<?= site_url('admin/kerja/update') ?>/" + id;
                const DELETE_URL = id => "<?= site_url('admin/kerja/delete') ?>/" + id;

                const ppatOptions = ['AJB', 'Hibah', 'Turun Waris', 'APHT', 'PPJB'];
                const notarisOptions = ['CV', 'PT', 'Pergantian Pengurus', 'PJB', 'SKMHT', 'Waarmerking', 'Legalisasi'];

                const btnWorkPanel = document.getElementById('btnWorkPanel');
                const workPanel = document.getElementById('work-panel');
                const btnWorkBack = document.getElementById('btnWorkBack');
                const btnAddWork = document.getElementById('btnAddWork');
                const sectionPPAT = document.getElementById('work-ppat');
                const sectionNot = document.getElementById('work-notaris');

                const modal = document.getElementById('work-modal');
                const modalTitle = document.getElementById('workModalTitle');
                const modalClose = document.getElementById('workModalClose');
                const form = document.getElementById('workForm');
                const fId = document.getElementById('workId');
                const fCat = document.getElementById('workCategory');
                const fSub = document.getElementById('workSubtype');
                const fTitle = document.getElementById('workTitle');
                const fNotes = document.getElementById('workNotes');
                const fFile = document.getElementById('workFile');
                const btnCancel = document.getElementById('workCancel');

                const openWork = () => { workPanel?.classList.remove('hidden'); document.body.style.overflow = 'hidden'; loadAll(); };
                const closeWork = () => { workPanel?.classList.add('hidden'); document.body.style.overflow = ''; };
                const openModal = (isEdit = false) => { if (!modal || !modalTitle) return; modal.classList.remove('hidden'); modalTitle.textContent = isEdit ? 'Edit File' : 'Tambah File'; };
                const closeModal = () => { if (!modal || !form || !fId) return; modal.classList.add('hidden'); form.reset(); fId.value = ''; refreshSubtype(); };

                const refreshSubtype = () => { if (!fSub || !fCat) return; const opts = (fCat.value === 'Notaris') ? notarisOptions : ppatOptions; fSub.innerHTML = opts.map(o => `<option value="${o}">${o}</option>`).join(''); };

                const groupTable = (group) => {
                    const rows = (group.items || []).map((it, idx) => `
      <tr class="border-b last:border-0">
        <td class="px-3 py-2 text-center">${idx + 1}</td>
        <td class="px-3 py-2">
          <div class="font-medium truncate">${it.title || it.filename}</div>
          ${it.notes ? `<div class="text-xs text-gray-500 truncate">${(it.notes || '').replace(/</g, '&lt;')}</div>` : ``}
        </td>
        <td class="px-3 py-2 text-gray-500 whitespace-nowrap">${it.mtime_text || '-'}</td>
        <td class="px-3 py-2 whitespace-nowrap">
          <div class="flex items-center justify-end gap-2">
            <button type="button" class="px-3 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-sm js-preview"
                    data-url="${it.url_preview || it.url_download || it.url}"
                    data-name="${it.title || it.filename}">
              <i class="fa-solid fa-eye"></i> Preview
            </button>
            <a class="px-3 py-1 rounded bg-white border hover:bg-gray-50 text-sm" href="${it.url_download || it.url_preview || it.url}">
              <i class="fa-solid fa-download"></i> Download
            </a>
            <button class="px-3 py-1 rounded bg-white border hover:bg-gray-50 text-sm" data-edit="${it.id}">
              <i class="fa-solid fa-pen"></i> Edit
            </button>
            <button class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-sm" data-del="${it.id}">
              <i class="fa-solid fa-trash"></i> Hapus
            </button>
          </div>
        </td>
      </tr>`).join('');

                    return `
      <div class="mb-6 rounded-lg border overflow-hidden bg-white">
        <div class="px-4 py-2 bg-gray-50 font-semibold flex items-center justify-between">
          <span>${group.subtype}</span>
          <span class="text-xs text-gray-500">${(group.items || []).length} file</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-3 py-2 w-14 text-center">No</th>
                <th class="px-3 py-2 text-left">Nama / Catatan</th>
                <th class="px-3 py-2 text-left">Diunggah</th>
                <th class="px-3 py-2 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody>${rows || ''}</tbody>
          </table>
        </div>
      </div>`;
                };

                async function loadFeed(category) {
                    const cont = (category === 'PPAT') ? sectionPPAT : sectionNot;
                    if (!cont) return;
                    try {
                        const res = await fetch(`${FEED_URL}?category=${encodeURIComponent(category)}`, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        cont.innerHTML = '';
                        const groups = (data.groups || []).filter(g => g.category === category);
                        if (!groups.length) { cont.innerHTML = `<div class="border rounded-lg p-6 text-gray-500 bg-gray-50">Belum ada data.</div>`; return; }
                        groups.forEach(g => cont.insertAdjacentHTML('beforeend', groupTable(g)));
                        cont.querySelectorAll('[data-edit]').forEach(btn => btn.addEventListener('click', () => openEdit(parseInt(btn.dataset.edit, 10))));
                        cont.querySelectorAll('[data-del]').forEach(btn => btn.addEventListener('click', () => doDelete(parseInt(btn.dataset.del, 10))));
                    } catch (e) {
                        cont.innerHTML = `<div class="border rounded-lg p-6 text-red-700 bg-red-50">Gagal memuat data.</div>`;
                    }
                }
                async function loadAll() { await Promise.all([loadFeed('PPAT'), loadFeed('Notaris')]); }

                function openAdd() { if (!fId || !fTitle || !fNotes || !fFile || !fCat) return; fId.value = ''; fTitle.value = ''; fNotes.value = ''; fFile.value = ''; fCat.value = 'PPAT'; refreshSubtype(); openModal(false); }
                async function openEdit(id) {
                    if (!modal) return;
                    const res = await fetch(FEED_URL, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    const all = (data.groups || []).flatMap(g => (g.items || []));
                    const it = all.find(x => x.id === id); if (!it) return alert('Data tidak ditemukan.');
                    const group = (data.groups || []).find(g => (g.items || []).some(x => x.id === id));
                    if (fId) fId.value = id;
                    if (fCat) fCat.value = group ? group.category : 'PPAT';
                    refreshSubtype();
                    if (fSub) fSub.value = group ? group.subtype : (fSub.value || '');
                    if (fTitle) fTitle.value = it.title || '';
                    if (fNotes) fNotes.value = it.notes || '';
                    if (fFile) fFile.value = '';
                    openModal(true);
                }
                async function doDelete(id) {
                    if (!confirm('Hapus file ini?')) return;
                    try {
                        const res = await fetch(DELETE_URL(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '<?= csrf_hash() ?>' } });
                        let ok = false; try { ok = (await res.json()).ok; } catch { }
                        if (!ok) return alert('Gagal menghapus.');
                        await loadAll();
                    } catch (e) { alert('Gagal menghapus.'); }
                }

                btnWorkPanel?.addEventListener('click', openWork);
                btnWorkBack?.addEventListener('click', closeWork);
                btnAddWork?.addEventListener('click', openAdd);
                modalClose?.addEventListener('click', closeModal);
                btnCancel?.addEventListener('click', closeModal);
                fCat?.addEventListener('change', refreshSubtype);
                refreshSubtype();

                form?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const id = (fId?.value || '').trim();
                    const fd = new FormData(form);
                    const url = id ? UPDATE_URL(id) : UPLOAD_URL;
                    try {
                        const res = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        let js = {}; try { js = await res.json(); } catch { }
                        if (!res.ok || !js || js.ok === false) { const msg = (js && js.error) ? js.error : ('HTTP ' + res.status); alert('Gagal menyimpan.\n\n' + msg); return; }
                        closeModal(); await loadAll();
                    } catch (err) { alert('Gagal menyimpan.\n\nNetwork/JS error: ' + err.message); }
                });
            })();

        // ===== ADMIN PANEL (overlay) – ARSIP =====
        (() => {
            const ARSIP_FEED = "<?= site_url('admin/arsip/feed') ?>";
            const ARSIP_UPLOAD = "<?= site_url('admin/arsip/upload') ?>";
            const ARSIP_DELETE = id => "<?= site_url('admin/arsip/delete') ?>/" + id;
            const ARSIP_UPDATE = id => "<?= site_url('admin/arsip/update') ?>/" + id; // <— endpoint UPDATE
            const ARSIP_REPORT = m => "<?= site_url('admin/arsip/report') ?>?month=" + encodeURIComponent(m);

            const csrfName = "<?= csrf_token() ?>";
            const csrfVal = "<?= csrf_hash() ?>";

            const btnOpen = document.getElementById('btnAdminPanel');
            const btnClose = document.getElementById('btnAdminClose');
            const panel = document.getElementById('admin-panel');

            const monthInp = document.getElementById('arsipMonth');
            const btnReport = document.getElementById('btnArsipReport');
            const btnAdd = document.getElementById('btnArsipAdd');
            const modal = document.getElementById('arsipModal');
            const modalTitle = document.getElementById('arsipModalTitle');
            const modalClose = document.getElementById('arsipClose');
            const modalCancel = document.getElementById('arsipCancel');
            const form = document.getElementById('arsipForm');
            const grid = document.getElementById('arsipGrid');
            const fileInput = form?.querySelector('input[name="file"]');
            const fileHint = document.getElementById('arsipFileHint');
            const submitBtn = document.getElementById('arsipSubmit');

            const masukBody = document.getElementById('arsipMasukBody');
            const keluarBody = document.getElementById('arsipKeluarBody');

            const uploaderName = "<?= esc((string) (session('nama') ?? 'Admin')) ?>";
            const jenisSel = form?.querySelector('select[name="jenis"]');
            const pihakInp = form?.querySelector('input[name="pihak"]');

            let arsipCache = null;         // cache data feed terakhir
            let arsipMode = 'add';        // 'add' | 'edit'
            let arsipEditId = null;        // id saat edit

            function updateReportLink() {
                const m = monthInp?.value || '<?= date('Y-m') ?>';
                if (btnReport) btnReport.href = ARSIP_REPORT(m);
            }

            const open = () => { panel?.classList.remove('hidden'); document.body.style.overflow = 'hidden'; loadArsip(); updateReportLink(); };
            const close = () => { panel?.classList.add('hidden'); document.body.style.overflow = ''; };

            function ensureReasonField(show) {
                let reasonWrap = document.getElementById('arsipReasonWrap');
                if (show) {
                    if (!reasonWrap) {
                        reasonWrap = document.createElement('div');
                        reasonWrap.id = 'arsipReasonWrap';
                        reasonWrap.className = 'md:col-span-2';
                        reasonWrap.innerHTML = `
                        <label class="text-sm">Alasan Perubahan</label>
                        <input type="text" name="reason" class="w-full border rounded-lg px-3 py-2" required>
                        <p class="text-xs text-gray-500 mt-1">Wajib diisi saat mengubah data.</p>`;
                        grid?.appendChild(reasonWrap);
                    }
                } else {
                    reasonWrap?.remove();
                }
            }

            function setModalMode(mode) {
                arsipMode = mode;
                if (mode === 'add') {
                    modalTitle.textContent = 'Upload Surat';
                    submitBtn.textContent = 'Simpan';
                    ensureReasonField(false);
                    if (fileInput) { fileInput.required = true; fileInput.value = ''; }
                    if (fileHint) fileHint.textContent = 'PDF/gambar/dokumen maks 10MB.';
                } else {
                    modalTitle.textContent = 'Edit Surat';
                    submitBtn.textContent = 'Update';
                    ensureReasonField(true);
                    if (fileInput) { fileInput.required = false; fileInput.value = ''; }
                    if (fileHint) fileHint.textContent = 'Kosongkan jika file tidak diganti.';
                }
            }

            function openModalAdd() {
                arsipEditId = null;
                setModalMode('add');
                form?.reset();
                // autofill pihak untuk surat masuk jika kosong
                maybeAutofillPihak();
                modal?.classList.remove('hidden');
            }
            function openModalEdit(item, jenis) {
                arsipEditId = item.id;
                setModalMode('edit');
                // Prefill
                form.querySelector('select[name="jenis"]').value = (item.jenis || jenis || 'masuk');
                form.querySelector('input[name="tanggal"]').value = (item.tanggal || '').slice(0, 10);
                const nomor = item.nomor_surat || item.no_surat || item.nomor || '';
                form.querySelector('input[name="nomor_surat"]').value = nomor;
                form.querySelector('input[name="pihak"]').value = item.pihak || '';
                form.querySelector('input[name="perihal"]').value = item.perihal || '';
                modal?.classList.remove('hidden');
            }
            function closeModal() {
                modal?.classList.add('hidden');
                form?.reset();
                setModalMode('add');
                arsipEditId = null;
            }

            function maybeAutofillPihak() {
                if (!jenisSel || !pihakInp) return;
                if (jenisSel.value === 'masuk' && !(pihakInp.value || '').trim()) { pihakInp.value = uploaderName; }
            }
            jenisSel?.addEventListener('change', maybeAutofillPihak);

            async function loadArsip() {
                if (!masukBody || !keluarBody) return;
                try {
                    const m = monthInp?.value || '<?= date('Y-m') ?>';
                    const res = await fetch(ARSIP_FEED + '?month=' + encodeURIComponent(m), { headers: { 'Accept': 'application/json' } });
                    const js = await res.json();
                    if (!js.ok) throw 0;
                    arsipCache = js;

                    const renderRows = (arr, jenis) => {
                        if (!arr.length) return `<tr><td colspan="6" class="px-3 py-4 text-gray-500">Belum ada data.</td></tr>`;
                        return arr.map((r, idx) => `
          <tr class="border-b last:border-0">
            <td class="px-3 py-2 text-center">${idx + 1}</td>
            <td class="px-3 py-2 whitespace-nowrap">${r.tanggal}</td>
            <td class="px-3 py-2">${(r.nomor || r.no_surat || r.nomor_surat || '-')}</td>
            <td class="px-3 py-2">${r.perihal || '-'}</td>
            <td class="px-3 py-2">${r.pihak || '-'}</td>
            <td class="px-3 py-2">
              <div class="flex items-center justify-end gap-2">
                <button class="px-3 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs js-preview" data-url="${r.url}" data-name="${r.perihal || 'File'}"><i class="fa-solid fa-eye"></i> Preview</button>
                <button class="px-3 py-1 rounded bg-white border hover:bg-gray-50 text-xs" data-arsip-edit="${r.id}" data-jenis="${jenis}"><i class="fa-solid fa-pen"></i> Edit</button>
                <a class="px-3 py-1 rounded bg-white border hover:bg-gray-50 text-xs" href="${r.url}" download><i class="fa-solid fa-download"></i> Download</a>
                <button class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs" data-arsip-del="${r.id}"><i class="fa-solid fa-trash"></i> Hapus</button>
              </div>
            </td>
          </tr>`).join('');
                    };

                    masukBody.innerHTML = renderRows(js.masuk || [], 'masuk');
                    keluarBody.innerHTML = renderRows(js.keluar || [], 'keluar');

                    // Delete
                    document.querySelectorAll('[data-arsip-del]').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            if (!confirm('Hapus file ini?')) return;
                            const id = btn.getAttribute('data-arsip-del');
                            let res = await fetch(ARSIP_DELETE(id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfVal } });
                            let ok = false; try { ok = (await res.json()).ok; } catch { }
                            if (!ok) {
                                res = await fetch(ARSIP_DELETE(id), {
                                    method: 'POST',
                                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: `${encodeURIComponent(csrfName)}=${encodeURIComponent(csrfVal)}`
                                });
                                try { ok = (await res.json()).ok; } catch { }
                            }
                            if (!ok) { alert('Gagal menghapus.'); return; }
                            loadArsip();
                        });
                    });

                    // Edit
                    document.querySelectorAll('[data-arsip-edit]').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const id = parseInt(btn.getAttribute('data-arsip-edit'), 10);
                            const jenis = btn.getAttribute('data-jenis') || '';
                            const arr = (jenis === 'keluar') ? (arsipCache?.keluar || []) : (arsipCache?.masuk || []);
                            const item = arr.find(x => String(x.id) === String(id));
                            if (!item) { alert('Data tidak ditemukan.'); return; }
                            openModalEdit(item, jenis);
                        });
                    });

                } catch (e) {
                    const errRow = '<tr><td colspan="6" class="px-3 py-4 text-red-700">Gagal memuat arsip.</td></tr>';
                    masukBody.innerHTML = errRow; keluarBody.innerHTML = errRow;
                }
            }

            // Modal helpers
            const openModal = () => modal?.classList.remove('hidden'); // tidak dipakai langsung
            const closeModalPublic = () => closeModal();

            // Events open/close
            btnOpen?.addEventListener('click', open);
            btnClose?.addEventListener('click', close);
            monthInp?.addEventListener('change', () => { updateReportLink(); loadArsip(); });

            btnAdd?.addEventListener('click', openModalAdd);
            modalClose?.addEventListener('click', closeModalPublic);
            modalCancel?.addEventListener('click', closeModalPublic);

            // Upload / Update submit
            form?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const fd = new FormData(form);
                let url = ARSIP_UPLOAD;
                if (arsipMode === 'edit') {
                    if (!arsipEditId) { alert('ID tidak valid.'); return; }
                    url = ARSIP_UPDATE(arsipEditId);
                    // pastikan ada reason
                    const reason = (fd.get('reason') || '').toString().trim();
                    if (!reason) { alert('Alasan perubahan wajib diisi.'); return; }
                }
                try {
                    const res = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const js = await res.json();
                    if (!res.ok || !js || js.ok === false) {
                        const msg = (js && (js.error || (js.errors && JSON.stringify(js.errors)))) || ('HTTP ' + res.status);
                        alert('Gagal menyimpan.\n\n' + msg); return;
                    }
                    closeModal();
                    loadArsip();
                } catch (err) {
                    alert('Gagal menyimpan.\n\nNetwork/JS error: ' + err.message);
                }
            });

            function closeModal() {
                modal?.classList.add('hidden');
                form?.reset();
                setModalMode('add');
                arsipMode = 'add';
                arsipEditId = null;
            }

            (function initReport() {
                const m = monthInp?.value || '<?= date('Y-m') ?>';
                const a = document.getElementById('btnArsipReport');
                if (a) a.href = "<?= site_url('admin/arsip/report') ?>?month=" + encodeURIComponent(m);
            })();

            function maybeAutofillPihak() { if (!jenisSel || !pihakInp) return; if (jenisSel.value === 'masuk' && !(pihakInp.value || '').trim()) { pihakInp.value = uploaderName; } }
        })();
    <?php endif; ?>

        // ===== navbar height -> panel top
        (function applyPanelsTop() {
            const header = document.querySelector('header, nav, .navbar, .topbar');
            function setTop() { const h = header ? Math.ceil(header.getBoundingClientRect().height) : 64; document.documentElement.style.setProperty('--navTop', h + 'px'); }
            setTop(); window.addEventListener('resize', setTop);
        })();

    // ===== preview modal universal
    (function () {
        const modal = document.getElementById('filePreviewModalTW');
        const bodyEl = document.getElementById('pmBodyTW');
        const titleEl = document.getElementById('pmTitleTW');
        const openEl = document.getElementById('pmOpenTW');
        const dlEl = document.getElementById('pmDownloadTW');

        function ext(url) { const clean = (url || '').split('?')[0].split('#')[0]; const m = clean.match(/\.([^.\/\\]+)$/i); return m ? m[1].toLowerCase() : ''; }
        const officeEmbed = url => 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(url);

        function show(url, name) {
            if (!modal || !bodyEl || !titleEl || !openEl || !dlEl) return;
            titleEl.textContent = name || 'Pratinjau Berkas';
            bodyEl.innerHTML = '';
            const e = ext(url);

            if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'].includes(e)) {
                bodyEl.innerHTML = '<div class="w-full h-full flex items-center justify-center bg-gray-50 overflow-auto p-4"></div>';
                const img = new Image(); img.src = url; img.alt = name || ''; img.className = 'max-w-full max-h-[66vh] rounded shadow';
                bodyEl.firstChild.appendChild(img);
            } else if (['pdf'].includes(e)) {
                const iframe = document.createElement('iframe'); iframe.src = url + '#zoom=page-fit'; iframe.className = 'w-full'; iframe.setAttribute('loading', 'lazy'); iframe.style.height = '70vh'; bodyEl.appendChild(iframe);
            } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(e)) {
                const iframe = document.createElement('iframe'); iframe.src = officeEmbed(url); iframe.className = 'w-full'; iframe.setAttribute('loading', 'lazy'); iframe.style.height = '70vh'; bodyEl.appendChild(iframe);
            } else {
                bodyEl.innerHTML = '<div class="p-6 text-sm text-gray-600">Preview tidak tersedia. Gunakan tombol di kanan.</div>';
            }

            openEl.href = url; dlEl.href = url;
            modal.classList.remove('hidden'); document.body.style.overflow = 'hidden';
        }
        function hide() { if (!modal || !bodyEl) return; modal.classList.add('hidden'); document.body.style.overflow = ''; bodyEl.innerHTML = ''; }

        document.addEventListener('click', function (e) {
            const t = e.target.closest('.js-preview');
            if (t) { e.preventDefault(); show(t.dataset.url, t.dataset.name); }
            if (e.target.hasAttribute('data-pm-close') || e.target.classList.contains('bg-black/50')) hide();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !document.getElementById('filePreviewModalTW')?.classList.contains('hidden')) hide(); });
    })();
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>