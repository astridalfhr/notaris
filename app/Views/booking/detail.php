<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
if (!function_exists('badgeClass')) {
    function badgeClass($st)
    {
        $s = strtolower((string) $st);
        return match ($s) {
            'confirmed' => 'bg-green-100 text-green-700',
            'completed', 'done' => 'bg-blue-100 text-blue-700',
            'cancelled', 'canceled' => 'bg-red-100 text-red-700',
            default => 'bg-yellow-100 text-yellow-700',
        };
    }
}

$status = strtolower((string) ($detail['status'] ?? ''));
$fmtTanggal = !empty($detail['tanggal']) ? date('d M Y', strtotime($detail['tanggal'])) : '-';

if (!empty($detail['jam'])) {
    $fmtJam = $detail['jam'];
} else {
    $mulai = (string) ($detail['jam_mulai'] ?? '');
    $selesai = (string) ($detail['jam_selesai'] ?? '');
    $fmtJam = trim($mulai . (($mulai && $selesai) ? ' - ' : '') . $selesai, ' -');
    if ($fmtJam === '')
        $fmtJam = '-';
}

$createdAt = !empty($detail['created_at']) ? date('d M Y, H:i', strtotime($detail['created_at'])) : '-';
$noTelp = $detail['no_telepon'] ?? ($detail['no_telp'] ?? null);

$request = \Config\Services::request();
$backParam = (string) $request->getGet('back');
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$baseHost = parse_url(base_url(), PHP_URL_HOST);
$refHost = $ref ? parse_url($ref, PHP_URL_HOST) : null;

$role = strtolower((string) (session('role') ?? 'user'));
switch (true) {
    case in_array($role, ['admin']):
        $fallbackBack = site_url('admin/dashboard');
        break;
    case in_array($role, ['multiuser', 'multi-user']):
        $fallbackBack = site_url('multiuser');
        break;
    case in_array($role, ['karyawan', 'pegawai', 'employee', 'staff']):
        $fallbackBack = site_url('admin/dashboard');
        break;
    default:
        $fallbackBack = site_url('user/dashboard');
        break;
}

if (!empty($backParam)) {
    if (preg_match('#^https?://#i', $backParam)) {
        $backUrl = $backParam;
    } else {
        $backPath = ltrim($backParam, '/');
        $backPath = preg_replace('#^index\.php/+?#', '', $backPath);
        $backUrl = site_url($backPath);
    }
} elseif ($ref && $refHost === $baseHost) {
    $backUrl = $ref;
} else {
    $backUrl = $fallbackBack;
}

$isAdmin = in_array($role, ['admin'], true);
$isEmployee = in_array($role, ['karyawan', 'pegawai', 'employee', 'staff'], true);
$isMultiuser = in_array($role, ['multiuser', 'multi-user', 'multi user'], true);

$cancelAction = site_url('booking/cancel/' . (int) ($detail['id'] ?? 0));
$confirmAction = null;

if ($isAdmin) {
    $confirmAction = site_url('admin/approve/' . (int) ($detail['id'] ?? 0));
    $cancelAction = site_url('admin/reject/' . (int) ($detail['id'] ?? 0));
}

if ($isMultiuser || $isEmployee) {
    $confirmAction = site_url('multiuser/dashboard/booking-confirm/' . (int) ($detail['id'] ?? 0));
}

$hiddenBack = $backUrl;
?>

<div class="container mx-auto px-6 py-10 max-w-4xl">
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-3">
                <i class="fas fa-file-signature text-yellow-500"></i>
                Detail Booking
            </h1>
            <span class="px-3 py-1 text-sm rounded-full <?= badgeClass($status) ?>">
                <?= esc(ucfirst($status)) ?>
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6 p-6">
            <div class="bg-gray-50 rounded-xl p-5">
                <h2 class="text-gray-700 font-semibold mb-3 flex items-center gap-2">
                    <i class="fas fa-user-tie text-gray-500"></i> Informasi Pegawai
                </h2>
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 flex-shrink-0 flex items-center justify-center">
                        <?php if (!empty($detail['karyawan_foto'])): ?>
                            <img src="<?= base_url('images/karyawan/' . $detail['karyawan_foto']) ?>"
                                alt="<?= esc($detail['karyawan_nama'] ?? 'Pegawai') ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-user text-gray-500 text-xl"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 text-lg"><?= esc($detail['karyawan_nama'] ?? '-') ?>
                        </div>
                        <div class="text-sm text-gray-600"><?= esc($detail['karyawan_jabatan'] ?? '-') ?></div>
                        <?php if (!empty($detail['karyawan_spesialisasi'])): ?>
                            <div class="text-xs text-gray-500 italic">Spesialis:
                                <?= esc($detail['karyawan_spesialisasi']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-5">
                    <div class="text-sm text-gray-500">Tanggal Konsultasi</div>
                    <div class="font-medium text-gray-800"><?= esc($fmtTanggal) ?></div>
                </div>
                <div class="mt-2">
                    <div class="text-sm text-gray-500">Jam</div>
                    <div class="font-medium text-gray-800"><?= esc($fmtJam) ?></div>
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-5">
                <h2 class="text-gray-700 font-semibold mb-3 flex items-center gap-2">
                    <i class="fas fa-id-card text-gray-500"></i> Data Pemesan
                </h2>
                <div class="space-y-2">
                    <div>
                        <div class="text-sm text-gray-500">Nama</div>
                        <div class="font-medium text-gray-800"><?= esc($detail['user_nama'] ?? '-') ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="font-medium text-gray-800"><?= esc($detail['user_email'] ?? '-') ?></div>
                    </div>
                    <?php if (!empty($noTelp)): ?>
                        <div>
                            <div class="text-sm text-gray-500">No. Telepon</div>
                            <div class="font-medium text-gray-800"><?= esc($noTelp) ?></div>
                        </div>
                    <?php endif; ?>
                    <div class="mt-4">
                        <div class="text-sm text-gray-500">Dibuat Pada</div>
                        <div class="font-medium text-gray-800"><?= esc($createdAt) ?></div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <h2 class="text-gray-700 font-semibold mb-3 flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-gray-500"></i> Catatan / Keluhan
                </h2>
                <div class="bg-white border border-gray-200 rounded-xl p-4 text-gray-700">
                    <?= nl2br(esc($detail['catatan'] ?? '-')) ?>
                </div>
            </div>
        </div>

        <div class="px-6 pb-6 flex flex-wrap items-center gap-3">
            <a href="<?= esc($backUrl) ?>"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">← Kembali</a>

            <?php if ($isAdmin || $isMultiuser || $isEmployee): ?>
                <?php if ($status === 'pending' && $confirmAction): ?>
                    <form action="<?= $confirmAction ?>" method="post" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="back" value="<?= esc($hiddenBack) ?>">
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">Konfirmasi</button>
                    </form>
                <?php endif; ?>

                <?php if ($isAdmin): ?>
                    <form action="<?= $cancelAction ?>" method="post" style="display:inline"
                        onsubmit="return confirm('Tolak / batalkan booking ini?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="back" value="<?= esc($hiddenBack) ?>">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Tolak</button>
                    </form>
                <?php endif; ?>

            <?php else: ?>
                <?php if (in_array($status, ['pending', 'confirmed', 'booked'], true)): ?>
                    <form action="<?= $cancelAction ?>" method="post" style="display:inline"
                        onsubmit="return confirm('Batalkan booking ini?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="back" value="<?= esc($hiddenBack) ?>">
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">Batalkan</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<?= $this->endSection() ?>