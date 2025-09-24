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
        --navTop: 64px
    }

    .panelBelowNav {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        top: var(--navTop)
    }

    /* tombol next/prev di modal */
    .pmNavBtn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 40px;
        height: 40px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #111827;
        color: #fff;
        opacity: .85;
    }

    .pmNavBtn:hover {
        opacity: 1
    }

    .pmNavPrev {
        left: 10px
    }

    .pmNavNext {
        right: 10px
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
                        <i class="fa-solid fa-screwdriver-wrench"></i> Laporan Bulanan
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
            <!-- ADMIN: JADWAL HARI INI -->
            <div class="mb-8">
                <div class="rounded-2xl border bg-white shadow-sm">
                    <div class="px-4 py-3 border-b bg-amber-50/60 rounded-t-2xl flex items-center justify-between">
                        <h3 class="font-semibold text-amber-700 flex items-center gap-2"><i
                                class="fa-solid fa-calendar-check"></i> Jadwal Hari Ini</h3>
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
                                <?php if (!empty($bookingsToday)):
                                    $i = 1;
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
                                                            <?= csrf_field() ?><input type="hidden" name="back" value="layanan">
                                                            <button
                                                                class="px-2.5 py-1 rounded bg-green-600 hover:bg-green-700 text-white text-xs"
                                                                type="submit">Confirm</button>
                                                        </form>
                                                        <form action="<?= site_url('admin/reject/' . $bid) ?>" method="post"
                                                            class="inline" onsubmit="return confirm('Tolak booking ini?');">
                                                            <?= csrf_field() ?><input type="hidden" name="back" value="layanan">
                                                            <button
                                                                class="px-2.5 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs"
                                                                type="submit">Cancel</button>
                                                        </form>
                                                    <?php elseif ($status === 'confirmed'): ?>
                                                        <?php if ($slotId > 0): ?>
                                                            <form action="<?= site_url('admin/slot/complete/' . $slotId) ?>" method="post"
                                                                class="inline" onsubmit="return confirm('Tandai sebagai selesai?');">
                                                                <?= csrf_field() ?><input type="hidden" name="back" value="layanan">
                                                                <button
                                                                    class="px-2.5 py-1 rounded bg-blue-600 hover:bg-blue-700 text-white text-xs"
                                                                    type="submit">Selesai</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-xs text-gray-400 italic">Tanpa slot</span>
                                                        <?php endif; ?>
                                                        <form action="<?= site_url('admin/reject/' . $bid) ?>" method="post"
                                                            class="inline" onsubmit="return confirm('Batalkan booking ini?');">
                                                            <?= csrf_field() ?><input type="hidden" name="back" value="layanan">
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
                                    <?php endforeach; else: ?>
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
                    $src = employee_photo_url($emp);
                    $specText = specs_to_string($emp['spesialisasi'] ?? '');
                    ?>
                    <div class="employee-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-500">
                        <img src="<?= esc($src) ?>" alt="<?= esc($emp['nama'] ?? 'Karyawan') ?>"
                            class="w-24 h-24 rounded-full mx-auto mb-4 object-cover"
                            onerror="this.src='https://www.gravatar.com/avatar/<?= md5(strtolower(trim($emp['email'] ?? ''))) ?>?s=200&d=identicon'" />
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

<!-- =================== PANELS & MODALS =================== -->
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
        <!-- Work panel (placeholder UI) -->
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
                        <div><label class="text-sm text-gray-700">Kategori</label>
                            <select name="category" id="workCategory" class="w-full border rounded-lg px-3 py-2">
                                <option value="PPAT">PPAT</option>
                                <option value="Notaris">Notaris</option>
                            </select>
                        </div>
                        <div><label class="text-sm text-gray-700">Sub-jenis</label><select name="subtype" id="workSubtype"
                                class="w-full border rounded-lg px-3 py-2"></select></div>
                        <div class="md:col-span-2"><label class="text-sm text-gray-700">Judul</label><input type="text"
                                name="title" id="workTitle" class="w-full border rounded-lg px-3 py-2"
                                placeholder="Judul dokumen (opsional)"></div>
                        <div class="md:col-span-2"><label class="text-sm text-gray-700">Catatan</label><textarea
                                name="notes" id="workNotes" rows="3" class="w-full border rounded-lg px-3 py-2"
                                placeholder="Catatan (opsional)"></textarea></div>
                        <div class="md:col-span-2"><label class="text-sm text-gray-700">File</label><input type="file"
                                name="file" id="workFile" class="block w-full text-sm">
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

        <!-- Admin panel (overlay) — ARSIP + LAPORAN -->
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
                                href="<?= site_url('admin/arsip/report?month=' . date('Y-m') . '&kat=ALL&all=1') ?>">
                                <i class="fa-solid fa-file-pdf"></i> Download Laporan Bulanan
                            </a>
                            <button id="btnArsipAdd" class="px-3 py-2 rounded-lg border bg-white text-sm">
                                <i class="fa-solid fa-file-circle-plus"></i> Upload Surat
                            </button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Surat Masuk -->
                        <div class="rounded-xl border bg-white">
                            <div class="px-4 py-3 bg-gray-50 font-semibold">Surat Masuk</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 w-14">No</th>
                                            <th class="px-3 py-2">Tanggal</th>
                                            <th class="px-3 py-2">Nomor Surat</th>
                                            <th class="px-3 py-2">Kategori</th>
                                            <th class="px-3 py-2">Perihal</th>
                                            <th class="px-3 py-2">Pengirim</th>
                                            <th class="px-3 py-2 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="arsipMasukBody">
                                        <tr>
                                            <td colspan="7" class="px-3 py-4 text-gray-500">Memuat…</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Surat Keluar -->
                        <div class="rounded-xl border bg-white">
                            <div class="px-4 py-3 bg-gray-50 font-semibold">Surat Keluar</div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 w-14">No</th>
                                            <th class="px-3 py-2">Tanggal</th>
                                            <th class="px-3 py-2">Nomor Surat</th>
                                            <th class="px-3 py-2">Kategori</th>
                                            <th class="px-3 py-2">Perihal</th>
                                            <th class="px-3 py-2">Penerima</th>
                                            <th class="px-3 py-2 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="arsipKeluarBody">
                                        <tr>
                                            <td colspan="7" class="px-3 py-4 text-gray-500">Memuat…</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ===================== LAPORAN BULANAN ===================== -->
                <section>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xl font-semibold">Laporan Bulanan (Notaris &amp; PPAT)</h3>
                        <div class="flex items-center gap-2">
                            <input type="month" id="lapMonth" class="border rounded-lg px-3 py-2 text-sm"
                                value="<?= esc(date('Y-m')) ?>">
                            <select id="lapKat" class="border rounded-lg px-3 py-2 text-sm">
                                <option value="ALL" selected>Semua Kategori</option>
                                <option value="NOTARIS">Notaris</option>
                                <option value="PPAT">PPAT</option>
                            </select>
                            <a id="btn-export-pdf"
                                class="px-3 py-2 rounded-lg bg-amber-600 text-white text-sm hover:bg-amber-700"
                                href="<?= site_url('admin/laporan/export?bulan=' . (int) date('m') . '&tahun=' . (int) date('Y') . '&kat=ALL&all=1') ?>">
                                <i class="fa-solid fa-file-pdf"></i> Download Laporan Bulanan
                            </a>
                            <button id="btn-open-modal-laporan"
                                class="px-3 py-2 rounded-lg border bg-white text-sm hover:bg-gray-50">
                                <i class="fa-solid fa-file-circle-plus"></i> Upload Laporan
                            </button>
                        </div>
                    </div>

                    <div id="laporan-table-wrap" class="border rounded p-3 bg-white">
                        <!-- NOTARIS -->
                        <div id="wrapNotaris">
                            <div class="font-semibold mb-2">Notaris</div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm mb-4" id="tbl-notaris">
                                    <thead>
                                        <tr class="text-left border-b bg-gray-50">
                                            <th class="px-2 py-2">No Bulan</th>
                                            <th class="px-2 py-2">Tanggal</th>
                                            <th class="px-2 py-2">Sifat</th>
                                            <th class="px-2 py-2">Nama Penghadap dan Atau Yang diwakili/Kuasa</th>
                                            <th class="px-2 py-2">Lampiran</th>
                                            <th class="px-2 py-2">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="px-3 py-3 text-gray-500">Memuat…</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- PPAT -->
                        <div id="wrapPPAT" class="mt-4">
                            <div class="font-semibold mb-2">PPAT</div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm" id="tbl-ppat">
                                    <thead>
                                        <tr class="text-left border-b bg-gray-50">
                                            <th class="px-2 py-2 align-middle" rowspan="2">No</th>
                                            <th class="px-2 py-2 text-center" colspan="2">Akta</th>
                                            <th class="px-2 py-2 align-middle" rowspan="2">Bentuk Perbuatan Hukum</th>
                                            <th class="px-2 py-2 text-center" colspan="2">Nama</th>
                                            <th class="px-2 py-2 text-center" rowspan="2">Jenis & Nomor Hak</th>
                                            <th class="px-2 py-2 align-middle" rowspan="2">Letak Tanah & Bangunan</th>
                                            <th class="px-2 py-2 text-center" colspan="2">Luas M²</th>
                                            <th class="px-2 py-2 align-middle" rowspan="2">Nilai Ht / Harga Transaksi
                                                Perolehan Pengalihan Hak</th>
                                            <th class="px-2 py-2 text-center" colspan="2">Sspt PBB</th>
                                            <th class="px-2 py-2 text-center" colspan="2">Ssp</th>
                                            <th class="px-2 py-2 align-middle" colspan="2">Bpthb</th>
                                            <th class="px-2 py-2 align-middle" rowspan="2">KET</th>
                                            <th class="px-2 py-2 align-middle" rowspan="2">Lampiran</th>
                                            <th class="px-2 py-2 align-middle text-right" rowspan="2">Aksi</th>
                                        </tr>
                                        <tr class="text-left border-b bg-gray-50">
                                            <!-- Akta -->
                                            <th class="px-2 py-2">No</th>
                                            <th class="px-2 py-2">Tggl</th>
                                            <!-- Nama -->
                                            <th class="px-2 py-2">Pihak Yang Mengalihkan/Memberi</th>
                                            <th class="px-2 py-2">Pihak Yang Menerima</th>
                                            <!-- Luas M² -->
                                            <th class="px-2 py-2">Tnh</th>
                                            <th class="px-2 py-2">Bgn</th>
                                            <!-- Sspt PBB -->
                                            <th class="px-2 py-2">NOP / Tahun</th>
                                            <th class="px-2 py-2">Njop</th>
                                            <!-- Ssp -->
                                            <th class="px-2 py-2">Tggl</th>
                                            <th class="px-2 py-2">Nilai (Rp. 000)</th>
                                            <!-- Bpthb -->
                                            <th class="px-2 py-2">Tggl</th>
                                            <th class="px-2 py-2">Nilai (Rp. 000)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="19" class="px-3 py-3 text-gray-500">Memuat…</td>
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
                        <div><label class="text-sm">Jenis</label><select name="jenis"
                                class="w-full border rounded-lg px-3 py-2">
                                <option value="masuk">Masuk</option>
                                <option value="keluar">Keluar</option>
                            </select></div>
                        <div><label class="text-sm">Kategori</label><select name="kat"
                                class="w-full border rounded-lg px-3 py-2">
                                <option value="NOTARIS">Notaris</option>
                                <option value="PPAT">PPAT</option>
                            </select></div>
                        <div><label class="text-sm">Tanggal</label><input type="date" name="tanggal"
                                class="w-full border rounded-lg px-3 py-2" value="<?= esc(date('Y-m-d')) ?>"></div>
                        <div><label class="text-sm">Nomor Surat</label><input type="text" name="nomor_surat"
                                class="w-full border rounded-lg px-3 py-2"></div>
                        <div><label class="text-sm">Pihak (Pengirim/Penerima)</label><input type="text" name="pihak"
                                class="w-full border rounded-lg px-3 py-2"></div>
                        <div class="md:col-span-2"><label class="text-sm">Perihal</label><input type="text" name="perihal"
                                class="w-full border rounded-lg px-3 py-2" required></div>
                        <div class="md:col-span-2"><label class="text-sm">File</label><input type="file" name="file"
                                class="block w-full text-sm" required>
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

        <!-- Laporan Modal (scrollable) -->
        <div id="modal-laporan" class="hidden fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40"></div>
            <div
                class="relative bg-white max-w-5xl w-11/12 mx-auto mt-16 rounded-xl shadow p-6 max-h-[85vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h5 class="text-lg font-bold">Upload Laporan Pekerjaan Bulanan</h5>
                    <button id="close-modal-laporan" type="button"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200">✕</button>
                </div>

                <form id="laporan-form" method="post" action="<?= site_url('admin/laporan/store') ?>"
                    enctype="multipart/form-data" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-700">Kategori</label>
                            <select id="kat" name="kat" class="w-full border rounded-lg px-3 py-2">
                                <option value="NOTARIS">Notaris</option>
                                <option value="PPAT">PPAT</option>
                            </select>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-1/2">
                                <label class="text-sm text-gray-700">Bulan</label>
                                <select name="bulan" class="w-full border rounded-lg px-3 py-2">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>" <?= (int) date('m') === $i ? 'selected' : '' ?>><?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="w-1/2">
                                <label class="text-sm text-gray-700">Tahun</label>
                                <input name="tahun" type="number" class="w-full border rounded-lg px-3 py-2"
                                    value="<?= (int) date('Y') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- NOTARIS -->
                    <div id="form-notaris" class="mt-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div><label class="text-sm text-gray-700">Tanggal</label><input name="tanggal" type="date"
                                    class="w-full border rounded-lg px-3 py-2" value="<?= date('Y-m-d') ?>"></div>
                            <div><label class="text-sm text-gray-700">Sifat</label><input name="sifat" type="text"
                                    class="w-full border rounded-lg px-3 py-2" autocomplete="new-password"></div>

                            <!-- Repeater Nama Penghadap -->
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700">Nama Penghadap dan Atau Yang diwakili/Kuasa</label>

                                <!-- nilai final yang dikirim ke server (gabungan nama dipisah koma) -->
                                <input type="hidden" name="nama_penghadap" id="notarisNamesFinal">

                                <!-- wadah baris-baris nama -->
                                <div id="notarisNamesWrap" class="space-y-2">
                                    <div class="flex gap-2 js-name-item">
                                        <input type="text" class="w-full border rounded-lg px-3 py-2 js-name-input"
                                            placeholder="Nama penghadap">
                                        <button type="button"
                                            class="px-3 py-2 rounded-lg border js-name-del hidden">Hapus</button>
                                    </div>
                                </div>

                                <button type="button" id="btnAddNotarisName" class="mt-2 px-3 py-2 rounded-lg border">+
                                    Tambah nama</button>
                                <p class="text-xs text-gray-500 mt-1">Opsional. Tambahkan nama sebanyak yang diperlukan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- PPAT -->
                    <div id="form-ppat" class="mt-4 hidden">
                        <div class="grid md:grid-cols-2 gap-4">
                            <!-- Akta -->
                            <div>
                                <label class="text-sm text-gray-700">No Akta</label>
                                <input name="akta_no" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">Tanggal Akta</label>
                                <input name="akta_tgl" type="date" class="w-full border rounded-lg px-3 py-2"
                                    value="<?= date('Y-m-d') ?>">
                            </div>

                            <!-- Bentuk Perbuatan Hukum (Select + Lainnya) -->
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700">Bentuk Perbuatan Hukum</label>
                                <div class="flex gap-2">
                                    <select id="ppatBentukSel" class="w-1/2 border rounded-lg px-3 py-2">
                                        <option value="" hidden>Pilih…</option>
                                        <option value="AJB">AJB</option>
                                        <option value="Akta Hibah">Akta Hibah</option>
                                        <option value="APHB (Pembagian Hak Bersama)">APHB</option>
                                        <option value="Alih Wilayah">Alih Wilayah</option>
                                        <option value="Roya">Roya</option>
                                        <option value="Peningkatan Hak">Peningkatan Hak</option>
                                        <option value="Pelepasan Hak">Pelepasan Hak</option>
                                        <option value="Turun Hak">Turun Hak</option>
                                        <option value="Pemisahan">Pemisahan</option>
                                        <option value="Ubah Lahan Pertanian Jadi Lahan Pekarangan">Ubah Lahan Pertanian Jadi
                                            Lahan Pekarangan</option>
                                        <option value="Turun Hak">Laporan Bulanan</option>
                                        <option value="Pengecekan Sertifikat">Pengecekan Sertifikat</option>
                                        <option value="Turun Waris">Turun Waris</option>
                                        <option value="Pemilihan Data">Pemilihan Data</option>
                                        <option value="Cek Kawasan">Cek Kawasan</option>
                                        <option value="Validasi">Validasi</option>
                                        <option value="Cek Lokasi">Cek Lokasi</option>
                                        <option value="LAINNYA">Lainnya…</option>
                                    </select>
                                    <input id="ppatBentukOther" type="text" class="w-1/2 border rounded-lg px-3 py-2 hidden"
                                        placeholder="Isi bentuk lain…">
                                </div>
                                <!-- ini yang benar-benar dikirim ke server -->
                                <input type="hidden" name="bentuk" id="ppatBentukFinal">
                            </div>

                            <!-- Nama -->
                            <div>
                                <label class="text-sm text-gray-700">Pihak Yang Mengalihkan/Memberi</label>
                                <input name="pihak_pengalih" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">Pihak Yang Menerima</label>
                                <input name="pihak_penerima" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>

                            <!-- Jenis & Nomor Hak -->
                            <div>
                                <label class="text-sm text-gray-700">Jenis & Nomor Hak</label>
                                <input name="jenis_hak" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>

                            <!-- Letak -->
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700">Letak Tanah & Bangunan</label>
                                <input name="letak" type="text" class="w-full border rounded-lg px-3 py-2"
                                    placeholder="Alamat/Desa/Kel/Kec">
                            </div>

                            <!-- Luas M² -->
                            <div>
                                <label class="text-sm text-gray-700">Luas Tanah (m²)</label>
                                <input name="luas_tnh" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">Luas Bangunan (m²)</label>
                                <input name="luas_bgn" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>

                            <!-- Nilai Transaksi -->
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700">Nilai Ht / Harga Transaksi Perolehan Pengalihan Hak
                                    (Rp)</label>
                                <input name="nilai_transaksi" type="text"
                                    class="w-full border rounded-lg px-3 py-2 js-money" placeholder="Rp 0">
                            </div>

                            <!-- Sspt PBB -->
                            <div>
                                <label class="text-sm text-gray-700">SSPT PBB - Njop</label>
                                <input name="sspt_njop" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">SSPT PBB - NOP / Tahun</label>
                                <input name="sspt_tahun" type="number" class="w-full border rounded-lg px-3 py-2"
                                    value="<?= (int) date('Y') ?>">
                            </div>

                            <!-- Ssp -->
                            <div>
                                <label class="text-sm text-gray-700">SSP - Tanggal</label>
                                <input name="sep_tgl" type="date" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">SSP - Nilai (Rp)</label>
                                <input name="sep_nilai" type="text" class="w-full border rounded-lg px-3 py-2 js-money"
                                    placeholder="Rp 0">
                            </div>

                            <!-- Bpthb -->
                            <div>
                                <label class="text-sm text-gray-700">BPHTB - Tanggal</label>
                                <input name="bphtb_tgl" type="date" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="text-sm text-gray-700">BPHTB - Nilai (Rp)</label>
                                <input name="bphtb_nilai" type="text" class="w-full border rounded-lg px-3 py-2 js-money"
                                    placeholder="Rp 0">
                            </div>

                            <!-- Keterangan -->
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700">Keterangan</label>
                                <input name="ket" type="text" class="w-full border rounded-lg px-3 py-2">
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm text-gray-700">Lampiran (bisa multi-file)</label>
                        <input type="file" name="lampiran[]" multiple class="block w-full text-sm">
                        <p class="text-xs text-gray-500 mt-1">Lampiran tidak ikut diekspor ke PDF.</p>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" id="close-modal-laporan-2" class="px-3 py-2 rounded-lg border">Batal</button>
                        <button type="submit" class="px-3 py-2 rounded-lg bg-amber-600 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>

<!-- Preview Modal (dengan Next/Prev) -->
<div id="filePreviewModalTW" class="fixed inset-0 z-50 hidden" data-current-file-id="">
    <div class="absolute inset-0 bg-black/50"></div>
    <div class="relative mx-auto mt-14 w-11/12 max-w-5xl bg-white rounded-xl shadow">
        <button type="button" class="pmNavBtn pmNavPrev" id="pmPrevTW" title="Sebelumnya">‹</button>
        <button type="button" class="pmNavBtn pmNavNext" id="pmNextTW" title="Berikutnya">›</button>

        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h4 class="font-semibold text-lg break-all" id="pmTitleTW">Pratinjau Berkas</h4>
            <button type="button" class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200" data-pm-close>✕</button>
        </div>
        <div class="p-0" id="pmBodyTW" style="height:70vh; overflow:hidden;"></div>
        <div class="flex items-center justify-between gap-2 px-4 py-3 border-t">
            <div class="text-xs text-gray-500">Gunakan tombol di kanan untuk membuka/unduh. Juga bisa pakai keyboard ←
                →.</div>
            <div class="flex items-center gap-2">
                <a id="pmOpenTW" class="px-3 py-2 rounded bg-amber-500 hover:bg-amber-600 text-white text-sm"
                    target="_blank" rel="noopener">
                    <i class="fa-solid fa-up-right-from-square"></i> Lihat di Tab Baru</a>
                <a id="pmDownloadTW" class="px-3 py-2 rounded bg-white border hover:bg-gray-50 text-sm" download>
                    <i class="fa-solid fa-download"></i> Unduh</a>
                <button id="pmDeleteTW"
                    class="px-3 py-2 rounded bg-red-600 hover:bg-red-700 text-white text-sm js-del-lamp"
                    data-file-id="">
                    <i class="fa-solid fa-trash"></i> Hapus</button>
                <button type="button" class="px-3 py-2 rounded bg-gray-600 hover:bg-gray-700 text-white text-sm"
                    data-pm-close>
                    <i class="fa-solid fa-xmark"></i> Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    /* ===== util tanggal ===== */
    const tzOffsetFix = (d) => { const p = n => String(n).padStart(2, '0'); return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`; };
    const addDays = (dateStr, n) => { const d = new Date((dateStr || '<?= $todayPreset ?>') + 'T00:00:00'); d.setDate(d.getDate() + n); return tzOffsetFix(d); };

    /* ===== role & guard booking ===== */
    const ROLE = "<?= esc($currentRole) ?>";
    const CAN_BOOK = ROLE === 'user';

    /* ===== panel jadwal ===== */
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
            const box = document.getElementById("schedule-content"); if (!box) return;
            box.innerHTML = '';
            if (!rows.length) { box.innerHTML = `<div class="border p-4 rounded-lg bg-gray-50 text-gray-600">Belum ada slot untuk tanggal ini.</div>`; return; }
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

    // Guard booking
    document.addEventListener('click', function (e) {
        const a = e.target.closest('a[data-booking-link]');
        if (a && !CAN_BOOK) { e.preventDefault(); alert('Hanya akun user yang diperbolehkan melakukan booking.\nAdmin tidak diperbolehkan membuat booking.'); }
    });

    <?php if ($isAdmin): ?>
            /* ===== ADMIN PANEL – ARSIP (global scope) ===== */
            (() => {
                const ARSIP_FEED = "<?= site_url('admin/arsip/feed') ?>";
                const ARSIP_UPLOAD = "<?= site_url('admin/arsip/upload') ?>";
                const ARSIP_DELETE = id => "<?= site_url('admin/arsip/delete') ?>/" + id;
                const ARSIP_UPDATE = id => "<?= site_url('admin/arsip/update') ?>/" + id;
                const ARSIP_REPORT = (m, k) => "<?= site_url('admin/arsip/report') ?>?month=" + encodeURIComponent(m) + (k ? "&kat=" + encodeURIComponent(k) : "") + "&all=1";

                const csrfName = "<?= csrf_token() ?>";
                const csrfVal = "<?= csrf_hash() ?>";

                const btnOpen = document.getElementById('btnAdminPanel');
                const btnClose = document.getElementById('btnAdminClose');
                const panel = document.getElementById('admin-panel');

                const monthInp = document.getElementById('arsipMonth');
                const katSel = document.getElementById('arsipKat');
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

                let arsipCache = null, arsipMode = 'add', arsipEditId = null;

                function updateReportLink() {
                    const m = monthInp?.value || '<?= date('Y-m') ?>';
                    const k = katSel?.value || 'ALL';
                    if (btnReport) btnReport.href = ARSIP_REPORT(m, k);
                }

                const open = () => { panel?.classList.remove('hidden'); document.body.style.overflow = 'hidden'; loadArsip(); updateReportLink(); };
                const close = () => { panel?.classList.add('hidden'); document.body.style.overflow = ''; };

                function ensureReasonField(show) {
                    let reasonWrap = document.getElementById('arsipReasonWrap');
                    if (show) {
                        if (!reasonWrap) {
                            reasonWrap = document.createElement('div');
                            reasonWrap.id = 'arsipReasonWrap'; reasonWrap.className = 'md:col-span-2';
                            reasonWrap.innerHTML = `<label class="text-sm">Alasan Perubahan</label><input type="text" name="reason" class="w-full border rounded-lg px-3 py-2" required><p class="text-xs text-gray-500 mt-1">Wajib diisi saat mengubah data.</p>`;
                            grid?.appendChild(reasonWrap);
                        }
                    } else { reasonWrap?.remove(); }
                }

                function setModalMode(mode) {
                    arsipMode = mode;
                    if (mode === 'add') {
                        modalTitle.textContent = 'Upload Surat'; submitBtn.textContent = 'Simpan';
                        ensureReasonField(false); if (fileInput) { fileInput.required = true; fileInput.value = ''; }
                        if (fileHint) fileHint.textContent = 'PDF/gambar/dokumen maks 10MB.';
                    } else {
                        modalTitle.textContent = 'Edit Surat'; submitBtn.textContent = 'Update';
                        ensureReasonField(true); if (fileInput) { fileInput.required = false; fileInput.value = ''; }
                        if (fileHint) fileHint.textContent = 'Kosongkan jika file tidak diganti.';
                    }
                }

                function openModalAdd() { arsipEditId = null; setModalMode('add'); form?.reset(); maybeAutofillPihak(); modal?.classList.remove('hidden'); }
                function openModalEdit(item, jenis) {
                    arsipEditId = item.id; setModalMode('edit');
                    form.querySelector('select[name="jenis"]').value = (item.jenis || jenis || 'masuk');
                    form.querySelector('select[name="kat"]').value = (item.kat || 'NOTARIS');
                    form.querySelector('input[name="tanggal"]').value = (item.tanggal || '').slice(0, 10);
                    const nomor = item.nomor_surat || item.no_surat || item.nomor || '';
                    form.querySelector('input[name="nomor_surat"]').value = nomor;
                    form.querySelector('input[name="pihak"]').value = item.pihak || '';
                    form.querySelector('input[name="perihal"]').value = item.perihal || '';
                    modal?.classList.remove('hidden');
                }
                function closeModal() { modal?.classList.add('hidden'); form?.reset(); setModalMode('add'); arsipEditId = null; }

                function maybeAutofillPihak() { if (!jenisSel || !pihakInp) return; if (jenisSel.value === 'masuk' && !(pihakInp.value || '').trim()) { pihakInp.value = uploaderName; } }
                jenisSel?.addEventListener('change', maybeAutofillPihak);

                async function loadArsip() {
                    if (!masukBody || !keluarBody) return;
                    try {
                        const m = monthInp?.value || '<?= date('Y-m') ?>';
                        const res = await fetch(ARSIP_FEED + '?month=' + encodeURIComponent(m) + '&all=1', { headers: { 'Accept': 'application/json' } });
                        const js = await res.json(); if (!js.ok) throw 0; arsipCache = js;

                        const renderRows = (arr, jenis) => {
                            if (!arr.length) return `<tr><td colspan="7" class="px-3 py-4 text-gray-500">Belum ada data.</td></tr>`;
                            return arr.map((r, idx) => `
            <tr class="border-b last:border-0">
              <td class="px-3 py-2 text-center">${idx + 1}</td>
              <td class="px-3 py-2 whitespace-nowrap">${r.tanggal}</td>
              <td class="px-3 py-2">${(r.nomor || r.no_surat || r.nomor_surat || '-')}</td>
              <td class="px-3 py-2">${(r.kat || '-')}</td>
              <td class="px-3 py-2">${r.perihal || '-'}</td>
              <td class="px-3 py-2">${r.pihak || '-'}</td>
              <td class="px-3 py-2">
                <div class="flex items-center justify-end gap-2">
                  <button class="px-3 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs js-preview"
                          data-url="${r.url}" data-name="${(r.perihal || 'File')}" data-mime="${(r.mime || '')}" data-file-id="${r.id}">
                    <i class="fa-solid fa-eye"></i> Preview
                  </button>
                  <button class="px-3 py-1 rounded bg-white border hover:bg-gray-50 text-xs" data-arsip-edit="${r.id}" data-jenis="${jenis}">
                    <i class="fa-solid fa-pen"></i> Edit
                  </button>
                  <a class="px-3 py-1 rounded bg-white border hover:bg-gray-50 text-xs" href="${r.url}" download>
                    <i class="fa-solid fa-download"></i> Download
                  </a>
                  <button class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs" data-arsip-del="${r.id}">
                    <i class="fa-solid fa-trash"></i> Hapus
                  </button>
                </div>
              </td>
            </tr>`).join('');
                        };

                        const filterKat = (arr) => {
                            const k = (katSel?.value || 'ALL').toUpperCase();
                            if (k === 'ALL') return arr;
                            return (arr || []).filter(x => String(x.kat || '').toUpperCase() === k);
                        };

                        const masuk = filterKat(js.masuk || []);
                        const keluar = filterKat(js.keluar || []);

                        masukBody.innerHTML = renderRows(masuk, 'masuk');
                        keluarBody.innerHTML = renderRows(keluar, 'keluar');

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
                        const errRow = '<tr><td colspan="7" class="px-3 py-4 text-red-700">Gagal memuat arsip.</td></tr>';
                        masukBody.innerHTML = errRow;
                        keluarBody.innerHTML = errRow;
                    }
                }

                btnOpen?.addEventListener('click', open);
                btnClose?.addEventListener('click', close);
                monthInp?.addEventListener('change', () => { updateReportLink(); loadArsip(); });
                katSel?.addEventListener('change', () => { updateReportLink(); loadArsip(); });
                btnAdd?.addEventListener('click', openModalAdd);
                modalClose?.addEventListener('click', () => closeModal());
                modalCancel?.addEventListener('click', () => closeModal());

                form?.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const fd = new FormData(form);
                    let url = ARSIP_UPLOAD;
                    if (arsipMode === 'edit') {
                        if (!arsipEditId) { alert('ID tidak valid.'); return; }
                        url = ARSIP_UPDATE(arsipEditId);
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
                        closeModal(); loadArsip();
                    } catch (err) {
                        alert('Gagal menyimpan.\n\nNetwork/JS error: ' + err.message);
                    }
                });
            })();

        /* ===== LAPORAN BULANAN (Notaris & PPAT) – global scope ===== */
        (() => {
            const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
            const BASE = "<?= site_url('admin/laporan') ?>";
            const csrfName = "<?= csrf_token() ?>";
            const csrfVal = "<?= csrf_hash() ?>";

            const lapMonth = document.getElementById('lapMonth');
            const lapKat = document.getElementById('lapKat');
            const btnOpen = document.getElementById('btn-open-modal-laporan');
            const btnExport = document.getElementById('btn-export-pdf');

            const tblNotBody = document.querySelector('#tbl-notaris tbody');
            const tblPpatBody = document.querySelector('#tbl-ppat tbody');

            const modal = document.getElementById('modal-laporan');
            const close1 = document.getElementById('close-modal-laporan');
            const close2 = document.getElementById('close-modal-laporan-2');
            const form = document.getElementById('laporan-form');
            const katSel = document.getElementById('kat');
            const formNot = document.getElementById('form-notaris');
            const formPp = document.getElementById('form-ppat');

            // === TAMPIL/SEMBUNYI WRAPPER SESUAI KATEGORI ===
            function applySectionVisibility() {
                const v = (lapKat?.value || 'ALL').toUpperCase();
                const notWrap = document.getElementById('wrapNotaris');
                const ppatWrap = document.getElementById('wrapPPAT');
                if (!notWrap || !ppatWrap) return;

                if (v === 'NOTARIS') {
                    notWrap.style.display = '';
                    ppatWrap.style.display = 'none';
                } else if (v === 'PPAT') {
                    notWrap.style.display = 'none';
                    ppatWrap.style.display = '';
                } else {
                    // ALL
                    notWrap.style.display = '';
                    ppatWrap.style.display = '';
                }
            }

            // === NOTARIS: Repeater Nama Penghadap ===
            function initNotarisNamesUI() {
                const wrap = document.getElementById('notarisNamesWrap');
                const btnAdd = document.getElementById('btnAddNotarisName');
                const hidden = document.getElementById('notarisNamesFinal');
                if (!wrap || !btnAdd || !hidden) return null;

                function updateDeleteButtons() {
                    const items = wrap.querySelectorAll('.js-name-item');
                    items.forEach((row) => {
                        const del = row.querySelector('.js-name-del');
                        if (del) del.classList.toggle('hidden', items.length <= 1);
                    });
                }

                function serialize() {
                    const vals = Array.from(wrap.querySelectorAll('.js-name-input'))
                        .map(i => (i.value || '').trim())
                        .filter(v => v !== '');
                    hidden.value = vals.join(', ');
                }

                function addRow(preset = '') {
                    const row = document.createElement('div');
                    row.className = 'flex gap-2 js-name-item';
                    row.innerHTML = `
                        <input type="text" class="w-full border rounded-lg px-3 py-2 js-name-input" placeholder="Nama penghadap" value="${preset.replace(/"/g, '&quot;')}">
                        <button type="button" class="px-3 py-2 rounded-lg border js-name-del">Hapus</button>
                    `;
                wrap.appendChild(row);
                updateDeleteButtons();
            }

            function resetRows(presets = ['']) {
                wrap.innerHTML = '';
                if (!Array.isArray(presets) || presets.length === 0) presets = [''];
                presets.forEach(v => addRow(v));
                serialize();
            }

            btnAdd.addEventListener('click', () => { addRow(''); serialize(); });
            wrap.addEventListener('input', e => { if (e.target.classList.contains('js-name-input')) serialize(); });
            wrap.addEventListener('click', e => {
                if (e.target.classList.contains('js-name-del')) {
                    const row = e.target.closest('.js-name-item');
                    if (row) row.remove();
                    updateDeleteButtons();
                    serialize();
                }
            });

            return { serialize, resetRows, addRow, updateDeleteButtons };
        }
        let notarisNamesUI = null;
        function ensureNotarisNamesUI() { if (!notarisNamesUI) notarisNamesUI = initNotarisNamesUI(); return notarisNamesUI; }

        // === PPAT: kontrol "Bentuk" (select + lainnya) ===
        function initPpatBentukUI() {
            const sel = document.getElementById('ppatBentukSel');
            const other = document.getElementById('ppatBentukOther');
            const fin = document.getElementById('ppatBentukFinal');
            if (!sel || !other || !fin) return null;

            function syncHidden() {
                const v = (sel.value || '').trim();
                if (v === 'LAINNYA') {
                    other.classList.remove('hidden');
                    fin.value = (other.value || '').trim();
                } else {
                    other.classList.add('hidden');
                    fin.value = v;
                }
            }
            sel.addEventListener('change', syncHidden);
            other.addEventListener('input', () => { if ((sel.value || '') === 'LAINNYA') fin.value = other.value.trim(); });

            function prefillFromHidden() {
                const preset = (fin.value || '').trim();
                if (!preset) { sel.value = ''; other.value = ''; other.classList.add('hidden'); return; }
                const match = Array.from(sel.options).some(o => o.value === preset);
                if (match) { sel.value = preset; other.value = ''; other.classList.add('hidden'); }
                else { sel.value = 'LAINNYA'; other.value = preset; other.classList.remove('hidden'); }
                syncHidden();
            }

            prefillFromHidden();
            return { prefillFromHidden, syncHidden };
        }
        let bentukUI = null;
        function ensureBentukUI() { if (!bentukUI) bentukUI = initPpatBentukUI(); return bentukUI; }

        // === Money helpers (format Rp saat ketik, bersihkan sebelum submit) ===
        const onlyDigits = (s) => (s || '').toString().replace(/[^\d]/g, '');
        const fmtIDR = (digits) => {
            const v = onlyDigits(digits);
            if (!v) return '';
            return 'Rp ' + v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        };

        function bindMoneyLive() {
            const moneyEls = Array.from(modal.querySelectorAll('input.js-money'));
            moneyEls.forEach(el => {
                if (!el.dataset.moneyBound) {
                    el.addEventListener('input', () => {
                        const caretAtEnd = (el.selectionStart === el.value.length);
                        el.value = fmtIDR(el.value);
                        if (caretAtEnd) el.selectionStart = el.selectionEnd = el.value.length;
                    });
                    el.dataset.moneyBound = '1';
                }
                if (el.value) el.value = fmtIDR(el.value);
            });
        }
        function normalizeMoneyBeforeSubmit() {
            const moneyEls = Array.from(modal.querySelectorAll('input.js-money'));
            moneyEls.forEach(el => { el.value = onlyDigits(el.value); });
        }

        let cacheNot = [], cachePpt = [];
        let editId = null;

        function getBT() {
            const v = lapMonth?.value || '<?= date('Y-m') ?>';
            const [Y, M] = v.split('-'); return { bulan: parseInt(M || '1', 10), tahun: parseInt(Y || '<?= date('Y') ?>', 10) };
        }
        const listURL = () => { const { bulan, tahun } = getBT(); const k = lapKat?.value || 'ALL'; return `${BASE}/list?bulan=${bulan}&tahun=${tahun}&kat=${encodeURIComponent(k)}&all=1`; };
        const exportURL = () => { const { bulan, tahun } = getBT(); const k = lapKat?.value || 'ALL'; return `${BASE}/export?bulan=${bulan}&tahun=${tahun}&kat=${encodeURIComponent(k)}&all=1`; };

        function clearTables() {
            if (tblNotBody) tblNotBody.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-gray-500">Memuat…</td></tr>`;
            if (tblPpatBody) tblPpatBody.innerHTML = `<tr><td colspan="19" class="px-3 py-4 text-gray-500">Memuat…</td></tr>`;
        }

        function resetForm() {
            editId = null; if (!form) return;
            form.reset(); form.action = "<?= site_url('admin/laporan/store') ?>";
            if (katSel) katSel.value = 'NOTARIS';
            formNot?.classList.remove('hidden'); formPp?.classList.add('hidden');
            const file = form.querySelector('input[type="file"]'); if (file) file.value = '';
            ensureBentukUI()?.prefillFromHidden();
            bindMoneyLive();
            ensureNotarisNamesUI()?.resetRows(['']); // reset 1 baris kosong
        }

        function lampiranButtons(files) {
            if (!files || !files.length) return '-';
            return files.map(f => `
                                        <div class="inline-flex items-center gap-2 mr-2 mb-1">
                                          <button class="px-2 py-1 rounded bg-amber-600 hover:bg-amber-700 text-white text-xs js-preview"
                                                  data-url="${BASE}/open/${f.id}"
                                                  data-name="${(f.original || 'Lampiran').replace(/"/g, '')}"
                                                  data-file-id="${f.id}"
                                                  data-mime="${(f.mime || '').replace(/"/g, '')}">
                                            <i class="fa-solid fa-eye"></i> Preview
                                          </button>
                                          <a class="px-2 py-1 rounded bg-white border hover:bg-gray-50 text-xs" href="${BASE}/download/${f.id}">
                                            <i class="fa-solid fa-download"></i> Download
                                          </a>
                                          <button class="px-2 py-1 rounded bg-red-600 hover:bg-red-700 text-white text-xs js-del-lamp" data-file-id="${f.id}">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                          </button>
                                        </div>`).join('');
        }

        function renderNotaris(rows) {
            cacheNot = rows || [];
            if (!tblNotBody) return; tblNotBody.innerHTML = '';
            if (!rows.length) { tblNotBody.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-gray-500">Belum ada data.</td></tr>`; return; }
            rows.forEach(r => {
                const p = r.payload || {};
                const acts = IS_ADMIN ? `
                                          <div class="flex items-center justify-end gap-2">
                                            <button class="px-2 py-1 rounded bg-white border hover:bg-gray-50 text-xs js-edit" data-id="${r.id}" data-kat="NOTARIS">
                                              <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <button class="px-2 py-1 rounded bg-white border hover:bg-gray-50 text-xs js-del" data-id="${r.id}">
                                              <i class="fa-solid fa-trash text-red-600"></i> Hapus
                                            </button>
                                          </div>` : '-';
                tblNotBody.insertAdjacentHTML('beforeend', `
                                          <tr class="border-b last:border-0">
                                            <td class="px-3 py-2">${r.nomor_bulanan ?? ''}</td>
                                            <td class="px-3 py-2">${p.tanggal ?? ''}</td>
                                            <td class="px-3 py-2">${p.sifat ?? ''}</td>
                                            <td class="px-3 py-2">${p.nama_penghadap ?? ''}</td>
                                            <td class="px-3 py-2">${lampiranButtons(r.files)}</td>
                                            <td class="px-3 py-2">${acts}</td>
                                          </tr>`);
            });
        }

        function renderPPAT(rows) {
            cachePpt = rows || [];
            if (!tblPpatBody) return; tblPpatBody.innerHTML = '';
            if (!rows.length) { tblPpatBody.innerHTML = `<tr><td colspan="22" class="px-3 py-4 text-gray-500">Belum ada data.</td></tr>`; return; }
            rows.forEach(r => {
                const p = r.payload || {};
                const acts = IS_ADMIN ? `
                                          <div class="flex items-center justify-end gap-2">
                                            <button class="px-2 py-1 rounded bg-white border hover:bg-gray-50 text-xs js-edit" data-id="${r.id}" data-kat="PPAT">
                                              <i class="fa-solid fa-pen"></i> Edit
                                            </button>
                                            <button class="px-2 py-1 rounded bg-white border hover:bg-gray-50 text-xs js-del" data-id="${r.id}">
                                              <i class="fa-solid fa-trash text-red-600"></i> Hapus
                                            </button>
                                          </div>` : '-';
                tblPpatBody.insertAdjacentHTML('beforeend', `
                                          <tr class="border-b last:border-0">
                                            <td class="px-3 py-2">${r.row_no ?? ''}</td>
                                            <td class="px-3 py-2">${p.akta_no ?? ''}</td>
                                            <td class="px-3 py-2">${p.akta_tgl ?? ''}</td>
                                            <td class="px-3 py-2">${p.bentuk ?? ''}</td>
                                            <td class="px-3 py-2">${p.pihak_pengalih ?? ''}</td>
                                            <td class="px-3 py-2">${p.pihak_penerima ?? ''}</td>
                                            <td class="px-3 py-2">${p.jenis_hak ?? ''}</td>
                                            <td class="px-3 py-2">${p.letak ?? ''}</td>
                                            <td class="px-3 py-2">${p.luas_tnh ?? ''}</td>
                                            <td class="px-3 py-2">${p.luas_bgn ?? ''}</td>
                                            <td class="px-3 py-2">${p.nilai_transaksi ?? ''}</td>
                                            <td class="px-3 py-2">${p.sspt_tahun}</td>
                                            <td class="px-3 py-2">${p.njop ?? ''}</td>
                                            <td class="px-3 py-2">${p.sep_tgl ?? ''}</td>
                                            <td class="px-3 py-2">${p.sep_nilai ?? ''}</td>
                                            <td class="px-3 py-2">${p.bphtb_tgl ?? ''}</td>
                                            <td class="px-3 py-2">${p.bphtb_nilai ?? ''}</td>
                                            <td class="px-3 py-2">${p.ket ?? ''}</td>
                                            <td class="px-3 py-2">${lampiranButtons(r.files)}</td>
                                            <td class="px-3 py-2">${acts}</td>
                                          </tr>`);
            });
        }

        function bindActions() {
            document.querySelectorAll('.js-del').forEach(btn => {
                btn.addEventListener('click', async () => {
                    if (!confirm('Hapus entri ini?')) return;
                    const id = btn.getAttribute('data-id');
                    try {
                        const res = await fetch(`${BASE}/${id}`, { method: 'DELETE' });
                        const js = await res.json();
                        if (!js.ok) return alert(js.msg || 'Gagal menghapus');
                        loadList();
                    } catch { alert('Gagal menghapus.'); }
                });
            });

            document.querySelectorAll('.js-edit').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = parseInt(btn.getAttribute('data-id'), 10);
                    const kat = btn.getAttribute('data-kat');
                    const arr = (kat === 'NOTARIS') ? cacheNot : cachePpt;
                    const r = arr.find(x => x.id === id);
                    if (!r) return alert('Data tidak ditemukan');

                    editId = id;
                    form.action = `${BASE}/update/${id}`;
                    katSel.value = kat;

                    const set = (n, v) => { const el = form.querySelector(`[name="${n}"]`); if (el) el.value = (v ?? ''); };

                    if (kat === 'NOTARIS') {
                        formNot.classList.remove('hidden'); formPp.classList.add('hidden');
                        const p = r.payload || {};
                        set('tanggal', (p.tanggal || r.tanggal || '').slice(0, 10));
                        set('sifat', p.sifat);

                        // pecah string nama lama jadi beberapa baris
                        const existing = (p.nama_penghadap || '')
                            .split(/[,;\n]/).map(s => s.trim()).filter(Boolean);
                        ensureNotarisNamesUI()?.resetRows(existing.length ? existing : ['']);
                    } else {
                        formPp.classList.remove('hidden'); formNot.classList.add('hidden');
                        const p = r.payload || {};
                        set('akta_no', p.akta_no);
                        set('akta_tgl', (p.akta_tgl || '').slice(0, 10));
                        set('bentuk', p.bentuk);

                        set('pihak_pengalih', p.pihak_pengalih);
                        set('pihak_penerima', p.pihak_penerima);
                        set('jenis_hak', p.jenis_hak);
                        set('letak', p.letak);
                        set('luas_tnh', p.luas_tnh);
                        set('luas_bgn', p.luas_bgn);
                        set('nilai_transaksi', p.nilai_transaksi);
                        set('sspt_njop', p.sspt_njop);
                        set('sspt_tahun', p.sspt_tahun);
                        set('sep_tgl', (p.sep_tgl || '').slice(0, 10));
                        set('sep_nilai', p.sep_nilai);
                        set('bphtb_tgl', (p.bphtb_tgl || '').slice(0, 10));
                        set('bphtb_nilai', p.bphtb_nilai);
                        set('ket', p.ket);

                        ensureBentukUI()?.prefillFromHidden();
                        bindMoneyLive();
                    }
                    modal.classList.remove('hidden');
                });
            });
        }

        function bindLampiranDeleteButtons() {
            document.querySelectorAll('.js-del-lamp').forEach(btn => {
                if (btn.dataset.bound === '1') return;
                btn.dataset.bound = '1';
                btn.addEventListener('click', async () => {
                    const id = btn.getAttribute('data-file-id');
                    if (!id) return;
                    if (!confirm('Hapus lampiran ini?')) return;

                    let ok = false;
                    try {
                        const res = await fetch(`${BASE}/lampiran/${id}`, {
                            method: 'DELETE', headers: { 'X-CSRF-TOKEN': "<?= csrf_hash() ?>", 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const js = await res.json(); ok = js && js.ok;
                    } catch { }

                    if (!ok) {
                        try {
                            const res = await fetch(`${BASE}/lampiran/delete/${id}`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                                body: `${encodeURIComponent(csrfName)}=${encodeURIComponent(csrfVal)}`
                            });
                            const js = await res.json(); ok = js && js.ok;
                        } catch { }
                    }

                    if (!ok) { alert('Gagal menghapus lampiran.'); return; }

                    const pm = document.getElementById('filePreviewModalTW');
                    if (pm && !pm.classList.contains('hidden')) {
                        const curId = pm.getAttribute('data-current-file-id') || '';
                        if (String(curId) === String(id)) {
                            if (typeof PM_next === 'function' && PM_hasNext()) PM_next();
                            else document.querySelector('[data-pm-close]')?.click();
                        }
                    }
                    typeof loadList === 'function' && loadList();
                });
            });
        }

        async function loadList() {
            try {
                clearTables();
                const res = await fetch(listURL(), { headers: { 'Accept': 'application/json' } });
                const js = await res.json();
                if (!js.ok) throw 0;

                const k = (lapKat?.value || 'ALL').toUpperCase();
                const arrNot = (js.notaris || []);
                const arrPpt = (js.ppat || []);

                if (k === 'ALL' || k === 'NOTARIS') renderNotaris(arrNot); else renderNotaris([]);
                if (k === 'ALL' || k === 'PPAT') renderPPAT(arrPpt); else renderPPAT([]);

                if (btnExport) btnExport.href = exportURL();

                bindActions();
                bindLampiranDeleteButtons();
            } catch {
                if (tblNotBody) tblNotBody.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-red-700">Gagal memuat.</td></tr>`;
                if (tblPpatBody) tblPpatBody.innerHTML = `<tr><td colspan="22" class="px-3 py-4 text-red-700">Gagal memuat.</td></tr>`;
            }
        }

        btnOpen?.addEventListener('click', () => { resetForm(); modal?.classList.remove('hidden'); });
        function closeModal() { modal?.classList.add('hidden'); resetForm(); }
        close1?.addEventListener('click', closeModal);
        close2?.addEventListener('click', closeModal);

        katSel?.addEventListener('change', e => {
            const v = e.target.value;
            if (v === 'NOTARIS') {
                formNot?.classList.remove('hidden'); formPp?.classList.add('hidden');
                ensureNotarisNamesUI(); // pastikan UI ready saat pindah ke NOTARIS
            }
            else { formPp?.classList.remove('hidden'); formNot?.classList.add('hidden'); ensureBentukUI(); bindMoneyLive(); }
        });

        // === SUBMIT LAPORAN: sinkron notaris names, bentuk, & Rp SEBELUM FormData dibuat ===
        form?.addEventListener('submit', async (ev) => {
            ev.preventDefault();
            ensureNotarisNamesUI()?.serialize(); // gabungkan nama ke hidden
            ensureBentukUI()?.syncHidden();
            normalizeMoneyBeforeSubmit();

            try {
                const fd = new FormData(form);
                fd.append(csrfName, csrfVal);
                const res = await fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
                const js = await res.json();
                if (!res.ok || !js.ok) { alert((js && (js.error || js.msg)) || `HTTP ${res.status}`); return; }
                closeModal(); loadList();
            } catch (e) { alert('Gagal menyimpan.\n\n' + (e?.message || e)); }
        });

        lapMonth?.addEventListener('change', () => {
            if (btnExport) btnExport.href = exportURL();
            loadList();
            applySectionVisibility();   // <— tambahkan (opsional)
        });

        lapKat?.addEventListener('change', () => {
            if (btnExport) btnExport.href = exportURL();
            loadList();
            applySectionVisibility();   // <— PENTING: toggle tampilan pas ganti kategori
        });

        if (btnExport) btnExport.href = exportURL();
        loadList();
        applySectionVisibility();     // <— panggil saat awal

        // expose untuk dipakai di delete handler
        window.loadList = loadList;
        window.bindLampiranDeleteButtons = bindLampiranDeleteButtons;
    })();
    <?php endif; ?>

        /* ===== navbar height -> panel top ===== */
        (function () {
            const header = document.querySelector('header, nav, .navbar, .topbar');
            function setTop() { const h = header ? Math.ceil(header.getBoundingClientRect().height) : 64; document.documentElement.style.setProperty('--navTop', h + 'px'); }
            setTop(); window.addEventListener('resize', setTop);
        })();

    /* ===== preview modal + SLIDES (pakai MIME) ===== */
    (function () {
        const modal = document.getElementById('filePreviewModalTW');
        const bodyEl = document.getElementById('pmBodyTW');
        const titleEl = document.getElementById('pmTitleTW');
        const openEl = document.getElementById('pmOpenTW');
        const dlEl = document.getElementById('pmDownloadTW');
        const delEl = document.getElementById('pmDeleteTW');
        const prevBtn = document.getElementById('pmPrevTW');
        const nextBtn = document.getElementById('pmNextTW');

        let PM_list = []; // [{url,name,id,mime}]
        let PM_idx = 0;

        function PM_render(item) {
            const { url, name, id, mime } = item || {};
            if (!modal || !bodyEl || !titleEl || !openEl || !dlEl || !url) return;

            titleEl.textContent = name || 'Pratinjau Berkas';
            openEl.href = url;
            try { dlEl.href = url.replace('/open/', '/download/'); } catch { dlEl.href = url; }

            const lower = (mime || '').toLowerCase();
            bodyEl.innerHTML = '';

            if (lower === 'application/pdf') {
                const iframe = document.createElement('iframe');
                iframe.src = url + '#zoom=page-fit';
                iframe.style.height = '70vh'; iframe.style.width = '100%'; iframe.loading = 'lazy';
                bodyEl.appendChild(iframe);
            } else if (lower.startsWith('image/')) {
                const wrap = document.createElement('div');
                wrap.className = 'w-full h-full flex items-center justify-center bg-gray-50 overflow-auto p-4';
                const img = new Image(); img.src = url; img.alt = name || ''; img.className = 'max-w-full max-h-[66vh] rounded shadow';
                wrap.appendChild(img); bodyEl.appendChild(wrap);
            } else {
                const iframe = document.createElement('iframe');
                iframe.src = url; iframe.style.height = '70vh'; iframe.style.width = '100%'; iframe.loading = 'lazy';
                bodyEl.appendChild(iframe);
            }

            modal.setAttribute('data-current-file-id', id || '');
            if (delEl) { delEl.dataset.fileId = id || ''; delEl.style.display = id ? '' : 'none'; }

            prevBtn.style.visibility = (PM_idx > 0) ? 'visible' : 'hidden';
            nextBtn.style.visibility = (PM_idx < PM_list.length - 1) ? 'visible' : 'hidden';
        }

        function PM_openFrom(buttonEl) {
            const container = buttonEl.closest('tbody') || document.getElementById('admin-panel') || document;
            const all = Array.from(container.querySelectorAll('.js-preview'));
            PM_list = all.map(b => ({ url: b.dataset.url, name: b.dataset.name, id: b.dataset.fileId || '', mime: b.dataset.mime || '' }));
            PM_idx = Math.max(0, PM_list.findIndex(x => (x.id && x.id === (buttonEl.dataset.fileId || '')) || x.url === buttonEl.dataset.url));
            if (PM_idx === -1) PM_idx = 0;

            PM_render(PM_list[PM_idx]);
            modal.classList.remove('hidden'); document.body.style.overflow = 'hidden';
        }

        function PM_close() { modal.classList.add('hidden'); document.body.style.overflow = ''; bodyEl.innerHTML = ''; modal.setAttribute('data-current-file-id', ''); }
        function PM_prev() { if (PM_idx > 0) { PM_idx--; PM_render(PM_list[PM_idx]); } }
        function PM_next() { if (PM_idx < PM_list.length - 1) { PM_idx++; PM_render(PM_list[PM_idx]); } }
        function PM_hasNext() { return PM_idx < PM_list.length - 1; }

        window.PM_next = PM_next;
        window.PM_hasNext = PM_hasNext;

        document.addEventListener('click', function (e) {
            const t = e.target.closest('.js-preview');
            if (t) { e.preventDefault(); PM_openFrom(t); }
            if (e.target.hasAttribute('data-pm-close') || e.target.classList.contains('bg-black/50')) PM_close();
            if (e.target === prevBtn) PM_prev();
            if (e.target === nextBtn) PM_next();
        });

        document.addEventListener('keydown', e => {
            if (modal.classList.contains('hidden')) return;
            if (e.key === 'Escape') PM_close();
            if (e.key === 'ArrowLeft') PM_prev();
            if (e.key === 'ArrowRight') PM_next();
        });
    })();
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>