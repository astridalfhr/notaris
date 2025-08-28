<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Data dari controller (tiga bucket)
$slotsActive = $slotsActive ?? [];
$slotsAvailable = $slotsAvailable ?? [];
$slotsCompleted = $slotsCompleted ?? [];

// Helper kecil untuk badge
function badge($derived)
{
    $d = strtolower((string) $derived);
    return match ($d) {
        'booked' => ['Booked', 'badge-booked'],
        'completed' => ['Completed', 'badge-completed'],
        default => ['Available', 'badge-available'],
    };
}

function fmt_date(?string $date): string
{
    return $date ? date('d M Y', strtotime($date)) : '-';
}
?>

<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>

    <main class="admin-main">
        <div class="slot-header">
            <div class="slot-title">
                <i class="fa-solid fa-calendar-plus"></i>
                <span>Kelola Slot</span>
            </div>
        </div>
        <?php
        $flashKeys = ['success', 'error', 'warning', 'info', 'message', 'status'];
        foreach ($flashKeys as $key):
            $msg = session()->getFlashdata($key);
            if (!$msg)
                continue;

            // mapping key -> kelas CSS yang sudah ada di slot.css
            $cls = match ($key) {
                'success', 'status' => 'success',
                'error' => 'danger',
                'warning' => 'warn',
                default => 'info',
            };

            // dukung array of messages
            if (is_array($msg))
                $msg = implode('<br>', array_map('esc', $msg));
            ?>
            <div class="slot-card" style="margin-top:12px">
                <div class="slot-alert <?= $cls ?>">
                    <i
                        class="fa-solid <?= $cls === 'success' ? 'fa-check-circle' : ($cls === 'danger' ? 'fa-triangle-exclamation' : 'fa-circle-info') ?>"></i>
                    <div><?= $msg ?></div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Form Tambah Slot -->
        <div class="slot-card">
            <form action="<?= site_url('multiuser/slot/store') ?>" method="post" id="slotForm" class="slot-form">
                <?= csrf_field() ?>
                <div class="slot-form-grid">
                    <div class="slot-field">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" value="<?= esc(old('tanggal')) ?>" required>
                    </div>
                    <div class="slot-field">
                        <label for="mulai">Dari jam</label>
                        <input type="time" id="mulai" name="mulai" step="300"
                            value="<?= esc(old('mulai') ?? '09:00') ?>" required>
                    </div>
                    <div class="slot-field">
                        <label for="sampai">Sampai jam</label>
                        <input type="time" id="sampai" name="sampai" step="300"
                            value="<?= esc(old('sampai') ?? '10:00') ?>" required>
                    </div>
                    <div class="slot-field note">
                        <small>Disimpan sebagai <b>jam</b> format <code>HH:MM–HH:MM</code>.</small>
                    </div>
                    <div class="slot-actions-right">
                        <button class="btn btn-primary-soft" type="submit">
                            <i class="fa-solid fa-circle-plus"></i> Tambah Slot
                        </button>
                    </div>
                </div>
            </form>

            <div class="slot-card">
                <div class="slot-card-head">
                    <h3>List Jadwal Booked (Aktif)</h3>
                </div>
                <div class="table-wrap table responsive">
                    <table class="slot-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($slotsActive): ?>
                                <?php $i = 1;
                                foreach ($slotsActive as $s):
                                    [$txt, $cls] = badge('booked'); ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= esc(fmt_date($s['tanggal'])) ?></td>
                                        <td><?= esc($s['jam']) ?></td>
                                        <td><span class="badge <?= $cls ?>"><?= $txt ?></span></td>
                                        <td>
                                            <div class="table-actions table responsive">
                                                <a href="<?= site_url('multiuser/slot/detail/' . (int) $s['jadwal_id']) ?>"
                                                    class="btn btn-gray">
                                                    <i class="fa-solid fa-circle-info"></i> Detail
                                                </a>
                                                <form action="<?= site_url('multiuser/slot/complete/' . (int) $s['jadwal_id']) ?>"
                                                    method="post"
                                                    onsubmit="return confirm('Tandai booking aktif sebagai selesai?');"
                                                    style="display:inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fa-solid fa-check"></i> Selesai
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty">Tidak ada slot yang sedang dibooking.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ======= TABEL 2: AVAILABLE ======= -->
            <div class="slot-card">
                <div class="slot-card-head">
                    <h3>List Jadwal Available</h3>
                </div>
                <div class="table-wrap table responsive">
                    <table class="slot-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($slotsAvailable): ?>
                                <?php $i = 1;
                                foreach ($slotsAvailable as $s):
                                    [$txt, $cls] = badge('available'); ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= esc(fmt_date($s['tanggal'])) ?></td>
                                        <td>
                                            <?= esc($s['jam']) ?>
                                            <?php if (!empty($s['note'])): ?>
                                                <div class="cell-note">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <span><?= esc($s['note']) ?></span>
                                                    <?php if (!empty($s['last_cancel_at'])): ?>
                                                        <span class="muted"> (dibatalkan:
                                                            <?= esc(date('d M Y H:i', strtotime($s['last_cancel_at']))) ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge <?= $cls ?>"><?= $txt ?></span></td>
                                        <td>
                                            <div class="table-actions">
                                                <form action="<?= site_url('multiuser/slot/delete/' . (int) $s['jadwal_id']) ?>"
                                                    method="post" onsubmit="return confirm('Hapus slot ini?');"
                                                    style="display:inline">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-danger">
                                                        <i class="fa-solid fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty">Tidak ada slot tersedia.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ======= TABEL 3: SELESAI (REKAP) ======= -->
            <div class="slot-card">
                <div class="slot-card-head">
                    <h3>List Jadwal Selesai</h3>
                </div>
                <div class="table-wrap table responsive">
                    <table class="slot-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($slotsCompleted): ?>
                                <?php $i = 1;
                                foreach ($slotsCompleted as $s):
                                    [$txt, $cls] = badge('completed'); ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= esc(fmt_date($s['tanggal'])) ?></td>
                                        <td><?= esc($s['jam']) ?></td>
                                        <td><span class="badge <?= $cls ?>"><?= $txt ?></span></td>
                                        <td>
                                            <a href="<?= site_url('multiuser/slot/detail/' . (int) $s['jadwal_id']) ?>"
                                                class="btn btn-gray">
                                                <i class="fa-solid fa-circle-info"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty">Belum ada rekapan yang selesai.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    </main>
</div>

<script>
    // Validasi sederhana "sampai jam" > "dari jam"
    document.getElementById('slotForm')?.addEventListener('submit', function (e) {
        const mulai = this.querySelector('#mulai')?.value;
        const sampai = this.querySelector('#sampai')?.value;
        if (mulai && sampai && sampai <= mulai) {
            e.preventDefault();
            alert('"Sampai jam" harus lebih besar dari "Dari jam".');
        }
    });

    // setTimeout(() => { document.querySelectorAll('.slot-alert').forEach(el => el.style.display = 'none'); }, 4000);
</script>

<link rel="stylesheet" href="<?= base_url('css/slot.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>