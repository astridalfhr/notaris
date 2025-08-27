<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1><?= esc($title) ?></h1>

<div>
    <h2>Informasi User</h2>
    <p><strong>Nama:</strong> <?= esc($user['name']) ?></p>
    <p><strong>Email:</strong> <?= esc($user['email']) ?></p>
</div>

<hr>

<div>
    <h2>Jadwal yang Sudah Dibooking</h2>
    <?php if (!empty($bookings)): ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>Jadwal ID</th>
                <th>Status</th>
                <th>Tanggal Booking</th>
            </tr>
            <?php foreach ($bookings as $index => $booking): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= esc($booking['nama']) ?></td>
                    <td><?= esc($booking['jadwal_id']) ?></td>
                    <td><?= esc($booking['status']) ?></td>
                    <td><?= esc($booking['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Belum ada jadwal yang dibooking.</p>
    <?php endif; ?>
</div>
