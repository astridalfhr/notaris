<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <?= $this->include('layouts/admin_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-calendar-check"></i> Detail Jadwal</h2>
            <a class="btn-grad" href="<?= site_url('admin/slot') ?>"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </div>

        <div class="card table responsive">
            <table class="table flat">
                <tbody>
                    <tr>
                        <th style="width:220px">Tanggal</th>
                        <td><?= esc(date('d M Y', strtotime($slot['tanggal']))) ?></td>
                    </tr>
                    <tr>
                        <th>Waktu</th>
                        <td><?= esc($slot['jam'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Status Booking</th>
                        <td><?= esc(ucfirst($booking['status'])) ?></td>
                    </tr>
                    <tr>
                        <th>Nama Pemesan</th>
                        <td><?= esc($booking['user_nama'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Email Pemesan</th>
                        <td><?= esc($booking['user_email'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Catatan</th>
                        <td><?= esc($booking['catatan'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Waktu Booking</th>
                        <td><?= esc(date('d M Y H:i', strtotime($booking['created_at']))) ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if (in_array($booking['status'], ['pending', 'approved'], true)): ?>
                <form action="<?= site_url('admin/slot/complete/' . (int) $slot['id']) ?>" method="post"
                    onsubmit="return confirm('Tandai jadwal ini sebagai selesai?');" style="margin-top:12px">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn success">
                        <i class="fa-solid fa-check"></i> Tandai Selesai
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>