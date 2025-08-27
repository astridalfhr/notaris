<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// ===== Normalisasi data dari controller agar view ini kompatibel =====

// 1) Summary -> Counts (termasuk mapping canceled -> cancelled & completed opsional)
$summary = $summary ?? [];
$counts = $counts ?? [
    'confirmed' => (int) ($summary['confirmed'] ?? 0),
    'pending' => (int) ($summary['pending'] ?? 0),
    'completed' => (int) ($summary['completed'] ?? 0),
    // Controller pakai 'canceled' (US); badge di CSS disiapkan 'cancelled' (UK)
    'cancelled' => (int) ($summary['canceled'] ?? 0),
];

// 2) Bookings: pakai bookingsToday kalau ada; jika tidak, pakai bookings
$bookingsToday = $bookingsToday ?? $bookings ?? [];

// 3) Slots: siapkan fallback slotsToday dari $slots (derived_status -> status)
if (!isset($slotsToday) || !is_array($slotsToday)) {
    $slotsToday = [];
    if (!empty($slots) && is_array($slots)) {
        foreach ($slots as $s) {
            $slotsToday[] = [
                'tanggal' => $s['tanggal'] ?? null,
                'jam' => $s['jam'] ?? null,
                // turunkan status dari derived_status agar badge di tabel tetap jalan
                'status' => $s['derived_status'] ?? ($s['status'] ?? 'available'),
            ];
        }
    }
}

// 4) Employee: pakai yang dari controller jika ada; fallback ke session
$employee = $employee ?? [];

// ====== Variabel tampilan header profil ======
$uri = uri_string();
$isRoot = ($uri === 'admin' || $uri === 'admin/');

$empName = (string) ($employee['nama'] ?? '');
$empEmail = (string) ($employee['email'] ?? '');

$displayName = $empName !== '' ? $empName : (string) (session('nama') ?? '-');
$displayEmail = $empEmail !== '' ? $empEmail : (string) (session('email') ?? '-');
$displayRole = isset($roleLabel) ? (string) $roleLabel : ucfirst((string) (session('role') ?? 'User'));
$joinedYear = isset($joinYear) ? (string) $joinYear : '-';
$totalAll = isset($totalBookings) ? (int) $totalBookings : 0;

// Foto: pakai employee['foto'] → images/karyawan/, atau URL dari profile_photo (Google)
$photo = (string) ($employee['foto'] ?? '');
$isUrl = $photo && filter_var($photo, FILTER_VALIDATE_URL);

// kalau foto bukan URL, anggap file lokal di images/karyawan
$localRel = 'images/karyawan/' . $photo;
$localAbs = FCPATH . $localRel;
$cacheBuster = '?v=' . time(); // cegah cache browser
?>

<div class="admin-layout">
    <?= $this->include('layouts/admin_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-gauge"></i> Dashboard Admin</h2>
            <a href="<?= site_url('admin/slot') ?>" class="btn-grad">
                <i class="fa-solid fa-plus"></i> Tambah Slot
            </a>
        </div>

        <div class="dashboard-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <div class="profile-avatar">
                        <?php if ($isUrl): ?>
                            <img src="<?= esc($photo) ?>" alt="Profile Photo" class="avatar-image">
                        <?php elseif ($photo && is_file($localAbs)): ?>
                            <img src="<?= base_url($localRel . $cacheBuster) ?>" alt="Profile Photo" class="avatar-image">
                        <?php else: ?>
                            <div class="default-avatar"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-details">
                        <h1 class="profile-name"><?= esc($displayName) ?></h1>
                        <p class="profile-email"><?= esc($displayEmail) ?></p>

                        <div class="profile-meta">
                            <div class="meta-item">
                                <span class="meta-number"><?= (int) $totalAll ?></span>
                                <span class="meta-label">Total Booking</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-number"><?= esc($joinedYear) ?></span>
                                <span class="meta-label">Bergabung</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-number"><?= esc($displayRole) ?></span>
                                <span class="meta-label">Status</span>
                            </div>
                        </div>

                        <a href="<?= base_url('admin/profile_edit') ?>" class="edit_profile-btn">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-num"><?= (int) ($counts['confirmed'] ?? 0) ?></div>
                    <div class="stat-lbl">Terkonfirmasi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= (int) ($counts['pending'] ?? 0) ?></div>
                    <div class="stat-lbl">Menunggu</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= (int) ($counts['completed'] ?? 0) ?></div>
                    <div class="stat-lbl">Selesai</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num"><?= (int) ($counts['cancelled'] ?? 0) ?></div>
                    <div class="stat-lbl">Batal</div>
                </div>
            </div>

            <!-- Jadwal Hari Ini -->
            <h3 class="block-title"><i class="fa-solid fa-calendar-check"></i> Jadwal Hari Ini</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pengguna</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Status</th>
                        <th style="width:220px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bookingsToday)): ?>
                        <?php $i = 1;
                        foreach ($bookingsToday as $b):
                            $tgl = !empty($b['tanggal']) ? date('d M Y', strtotime($b['tanggal'])) : '-';
                            $jam = ($tmp = trim((string) ($b['jam'] ?? ''))) !== '' ? $tmp : '-';

                            // Normalisasi status agar sesuai badge CSS yang ada
                            $raw = strtolower((string) ($b['status'] ?? 'pending'));
                            $status = match ($raw) {
                                'approve' => 'confirmed',
                                'approved' => 'confirmed',
                                'cancelled', 'cancel' => 'cancelled',
                                'done' => 'completed',
                                default => $raw,
                            };
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <strong><?= esc($b['user_nama'] ?? '-') ?></strong>
                                    <div class="subtext"><?= esc($b['user_email'] ?? '-') ?></div>
                                </td>
                                <td><?= esc($tgl) ?></td>
                                <td><?= esc($jam) ?></td>
                                <td><span class="badge <?= esc($status) ?>"><?= esc(ucfirst($status)) ?></span></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="<?= site_url('booking/detail/' . (int) $b['id']) ?>" class="btn small warn">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                        <?php if ($status === 'pending'): ?>
                                            <form action="<?= site_url('admin/approve/' . (int) $b['id']) ?>" method="post"
                                                style="display:inline">
                                                <?= csrf_field() ?>
                                                <button class="btn small success" type="submit">
                                                    <i class="fa-solid fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form action="<?= site_url('admin/reject/' . (int) $b['id']) ?>" method="post"
                                                style="display:inline" onsubmit="return confirm('Tolak booking ini?');">
                                                <?= csrf_field() ?>
                                                <button class="btn small danger" type="submit">
                                                    <i class="fa-solid fa-xmark"></i> Tolak
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="muted">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php elseif (!empty($slotsToday)): /* fallback: tampilkan slot jika belum ada booking */ ?>
                        <?php $i = 1;
                        foreach ($slotsToday as $s):
                            $tgl = !empty($s['tanggal']) ? date('d M Y', strtotime($s['tanggal'])) : '-';
                            $jam = !empty($s['jam']) ? $s['jam'] : '-';
                            $st = strtolower((string) ($s['status'] ?? 'available'));
                            ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><em class="muted">—</em></td>
                                <td><?= esc($tgl) ?></td>
                                <td><?= esc($jam) ?></td>
                                <td><span class="badge <?= esc($st) ?>"><?= esc(ucfirst($st)) ?></span></td>
                                <td><span class="muted">Belum dipesan</span></td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty">Tidak ada jadwal/slot yang sudah dibooking untuk hari ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>