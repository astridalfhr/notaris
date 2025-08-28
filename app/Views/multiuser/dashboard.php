<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>
    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-gauge"></i> Dashboard Multi-User</h2>
            <div class="muted">Tanggal: <?= esc(date('d M Y', strtotime($today))) ?></div>
        </div>

        <div class="dashboard-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-info">
                    <?php $photo = $user['profile_photo'] ?? ''; ?>
                    <div class="profile-avatar">
                        <?php if (!empty($photo)): ?>
                            <img src="<?= esc($photo) ?>" alt="Profile Photo" class="avatar-image">
                        <?php else: ?>
                            <div class="default-avatar"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-details">
                        <h1 class="profile-name"><?= esc($user['nama'] ?? '-') ?></h1>
                        <p class="profile-email"><?= esc($user['email'] ?? '-') ?></p>

                        <div class="profile-meta">
                            <div class="meta-item">
                                <span class="meta-number"><?= esc($totalAll ?? 0) ?></span>
                                <span class="meta-label">Total Booking</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-number"><?= esc($joinYear ?? '-') ?></span>
                                <span class="meta-label">Bergabung</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-number"><?= esc(ucfirst($user['role'] ?? 'User')) ?></span>
                                <span class="meta-label">Status</span>
                            </div>
                        </div>

                        <a href="<?= site_url('multiuser/profile_edit') ?>" class="edit_profile-btn">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistik Booking -->
            <?php
            $stat = $stat ?? ['today_all' => 0, 'today_me' => 0, 'month_all' => 0, 'month_me' => 0];
            ?>
            <div class="card">
                <h3 class="card-title"><i class="fa-solid fa-chart-column"></i> Statistik Booking</h3>
                <div class="grid" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;">
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;text-align:center;">
                        <div style="font-size:28px;font-weight:700;"><?= esc($stat['today_all']) ?></div>
                        <div class="muted">Hari Ini (Semua)</div>
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;text-align:center;">
                        <div style="font-size:28px;font-weight:700;"><?= esc($stat['today_me']) ?></div>
                        <div class="muted">Hari Ini (Saya)</div>
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;text-align:center;">
                        <div style="font-size:28px;font-weight:700;"><?= esc($stat['month_all']) ?></div>
                        <div class="muted">Bulan Ini (Semua)</div>
                    </div>
                    <div class="mini-card"
                        style="border:1px solid #eee;border-radius:12px;padding:16px;background:#fafafa;text-align:center;">
                        <div style="font-size:28px;font-weight:700;"><?= esc($stat['month_me']) ?></div>
                        <div class="muted">Bulan Ini (Saya)</div>
                    </div>
                </div>
            </div>

            <!-- Booking Groups Hari Ini -->
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $gid => $g): ?>
                    <div class="card">
                        <h3 class="card-title">
                            <i class="fa-solid fa-user-tie"></i>
                            <?= esc($g['karyawan_nama'] ?? 'Tanpa Nama') ?>
                        </h3>
                        <div class="table-responsive">
                            <table class="table flat">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Pengguna</th>
                                        <th>Jam</th>
                                        <th>Status</th>
                                        <th>Konfirmasi</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1;
                                    foreach ($g['items'] ?? [] as $row):
                                        $st = strtolower($row['status'] ?? 'pending');
                                        ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= esc($row['user_nama'] ?? '-') ?></td>
                                            <td><?= esc($row['jam'] ?? $row['jadwal_jam'] ?? '-') ?></td>
                                            <td><span class="badge <?= esc($st) ?>"><?= esc(ucfirst($st)) ?></span></td>

                                            <td>
                                                <?php if (($g['karyawan_id'] ?? 0) === ($myEmployeeId ?? -1) && $st === 'pending'): ?>
                                                    <form method="post"
                                                        action="<?= site_url('multiuser/dashboard/booking-confirm/' . (int) $row['id']) ?>"
                                                        style="display:inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="back" value="<?= esc(site_url('multiuser')) ?>">
                                                        <button class="btn small success"
                                                            onclick="return confirm('Konfirmasi booking ini?')">
                                                            Confirm
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <a class="btn small warn"
                                                    href="<?= site_url('booking/detail/' . (int) $row['id']) . '?back=' . urlencode(site_url('multiuser')) ?>">
                                                    Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card">
                    <div class="muted">Tidak ada jadwal hari ini.</div>
                </div>
            <?php endif; ?>

        </div>
    </main>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>