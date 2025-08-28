<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="dashboard-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-info">
            <?php
            // Siapkan sumber foto (URL atau file lokal)
            $photo = $user['profile_photo'] ?? '';
            $isUrl = $photo && filter_var($photo, FILTER_VALIDATE_URL);
            $localRel = 'uploads/profiles/' . $photo;
            $localAbs = FCPATH . $localRel;
            ?>
            <div class="profile-avatar">
                <?php if ($isUrl): ?>
                    <img src="<?= esc($photo) ?>" alt="Profile Photo" class="avatar-image">
                <?php elseif ($photo && is_file($localAbs)): ?>
                    <img src="<?= base_url($localRel) ?>" alt="Profile Photo" class="avatar-image">
                <?php else: ?>
                    <div class="default-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-details">
                <h1 class="profile-name"><?= esc($user['nama'] ?? '-') ?></h1>
                <p class="profile-email"><?= esc($user['email'] ?? '-') ?></p>

                <?php
                // Tampilkan tahun bergabung dari created_at
                $joinYear = '-';
                if (!empty($user['created_at']) && $user['created_at'] !== '0000-00-00 00:00:00') {
                    $ts = strtotime((string) $user['created_at']);
                    if ($ts !== false)
                        $joinYear = date('Y', $ts);
                }
                ?>

                <div class="profile-meta">
                    <div class="meta-item">
                        <span class="meta-number"><?= count($bookings ?? []) ?></span>
                        <span class="meta-label">Total Booking</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-number"><?= esc($joinYear) ?></span>
                        <span class="meta-label">Bergabung</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-number"><?= esc(ucfirst($user['role'] ?? 'User')) ?></span>
                        <span class="meta-label">Status</span>
                    </div>
                </div>

                <a href="<?= base_url('user/edit_profile') ?>" class="edit_profile-btn">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">
                <?= count(array_filter($bookings ?? [], static fn($b) => (strtolower($b['status'] ?? '') === 'confirmed'))) ?>
            </div>
            <div class="stat-label">Booking Terkonfirmasi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?= count(array_filter($bookings ?? [], static fn($b) => (strtolower($b['status'] ?? '') === 'pending'))) ?>
            </div>
            <div class="stat-label">Menunggu Konfirmasi</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?= count(array_filter($bookings ?? [], static fn($b) => (strtolower($b['status'] ?? '') === 'completed'))) ?>
            </div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>

    <!-- Booking Section -->
    <div class="section">
        <!-- Header + CTA selalu tampil -->
        <div class="section-head"
            style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px">
            <h2 class="section-title" style="margin:0">
                <i class="fas fa-calendar-alt"></i>
                Jadwal Booking Saya
            </h2>
            <a href="<?= site_url('layanan') ?>" class="booking-btn" style="text-decoration:none;">
                <i class="fas fa-plus"></i> Booking Jadwal Baru
            </a>
        </div>

        <?php if (!empty($bookings)): ?>
            <div class="table-responsive">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pegawai</th>
                            <th>Layanan</th>
                            <th>Status</th>
                            <th>Tanggal Booking</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $index => $booking): ?>
                            <?php
                            // Format tanggal booking dari created_at
                            $bCreated = $booking['created_at'] ?? null;
                            $bDate = '-';
                            if (!empty($bCreated) && $bCreated !== '0000-00-00 00:00:00') {
                                $bTs = strtotime((string) $bCreated);
                                if ($bTs !== false)
                                    $bDate = date('d M Y, H:i', $bTs);
                            }

                            // Fallback nama pegawai
                            $pegawaiNama = $booking['nama_pegawai']
                                ?? $booking['karyawan_nama']
                                ?? $booking['nama'] // jika join berbeda
                                ?? 'N/A';

                            $st = strtolower($booking['status'] ?? 'pending');
                            $id = (int) ($booking['id'] ?? 0);
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= esc($pegawaiNama) ?></strong></td>
                                <td><?= esc($booking['nama_layanan'] ?? 'Layanan') ?></td>
                                <td>
                                    <span class="status-badge status-<?= esc($st) ?>">
                                        <?= esc(ucfirst($st)) ?>
                                    </span>
                                </td>
                                <td><?= esc($bDate) ?></td>
                                <td>
                                    <?php if ($id): ?>
                                        <a href="<?= base_url('booking/detail/' . $id) ?>"
                                            style="color:#FFD700; text-decoration:none;">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#999;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3 class="empty-state-title">Belum Ada Jadwal yang Dibooking</h3>
                <p class="empty-state-text">Mulai booking layanan untuk melihat jadwal Anda disini</p>
                <a href="<?= base_url('layanan') ?>" class="booking-btn">
                    <i class="fas fa-plus"></i> Booking Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<a href="<?= site_url('layanan') ?>" class="mobile-fab" style="position:fixed;right:24px;bottom:24px;width:56px;height:56px;border-radius:50%;
          background:#FBBF24;color:#fff;display:none;align-items:center;justify-content:center;
          box-shadow:0 8px 20px rgba(0,0,0,.15);text-decoration:none;">
    <i class="fas fa-plus"></i>
</a>
<style>
    @media (max-width: 768px) {
        .mobile-fab {
            display: flex;
        }
    }
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?= $this->endSection() ?>