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

// BACK URL
$request = \Config\Services::request();
$backParam = (string) $request->getGet('back');
$ref = $_SERVER['HTTP_REFERER'] ?? '';
$baseHost = parse_url(base_url(), PHP_URL_HOST);
$refHost = $ref ? parse_url($ref, PHP_URL_HOST) : null;

$role = strtolower((string) (session('role') ?? 'user'));
switch (true) {
    case in_array($role, ['admin']):
        $fallbackBack = 'admin/dashboard';
        break;
    case in_array($role, ['multiuser', 'multi-user']):
        $fallbackBack = 'multiuser/slot';
        break;
    case in_array($role, ['karyawan', 'pegawai', 'employee', 'staff']):
        $fallbackBack = 'multiuser/slot';
        break;
    default:
        $fallbackBack = 'user/dashboard';
        break;
}

if (!empty($backParam)) {
    $backUrl = (str_starts_with($backParam, 'http://') || str_starts_with($backParam, 'https://'))
        ? site_url($fallbackBack)
        : site_url(ltrim($backParam, '/'));
} elseif ($ref && $refHost === $baseHost) {
    $backUrl = $ref;
} else {
    $backUrl = site_url($fallbackBack);
}
?>

<div class="container mx-auto px-6 py-10 max-w-4xl">
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-3">
                <i class="fas fa-file-signature text-yellow-500"></i>
                Detail Slot
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
                            <div class="text-xs text-gray-500 italic">
                                Spesialis: <?= esc($detail['karyawan_spesialisasi']) ?>
                            </div>
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
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<?= $this->endSection() ?>