<?php
use App\Libraries\KerjaMenu; // <-- biar bisa fallback panggil library

$uri = service('uri');
$path = trim($uri->getPath(), '/');
$pathN = ltrim(preg_replace('#^index\.php/#', '', $path), '/');

if (!function_exists('str_starts_with')) {
    function str_starts_with($h, $n)
    {
        return (string) $n !== '' && strncmp($h, $n, strlen($n)) === 0;
    }
}

$activeExact = static fn(string $p) => $pathN === trim($p, '/') ? 'is-active' : '';
$activeAny = static function (array $ps) use ($pathN) {
    foreach ($ps as $p)
        if ($pathN === trim($p, '/'))
            return 'is-active';
    return '';
};
$activeStart = static fn(string $prefix) => str_starts_with($pathN, trim($prefix, '/')) ? 'is-active' : '';

// --- Menu: pakai yang dikirim controller jika ada, kalau kosong -> fallback ke library
$menu = $menu ?? [];
$isMenuEmpty = empty($menu['ppat']) && empty($menu['notaris']);
if ($isMenuEmpty) {
    try {
        // fallback ringan: ambil dari sumber tunggal
        $menu = KerjaMenu::get();
    } catch (\Throwable $e) {
        $menu = ['ppat' => [], 'notaris' => []];
    }
}

// Buka group otomatis saat di path kerja/ppat atau kerja/notaris
$isPPATOpen = str_starts_with($pathN, 'admin/kerja/ppat');
$isNotarisOpen = str_starts_with($pathN, 'admin/kerja/notaris');
?>

<aside class="admin-sidebar">
    <div class="admin-brand"><i class="fa-solid fa-shield"></i><span>Admin Panel</span></div>

    <nav class="admin-nav">
        <a class="admin-nav__link <?= $activeAny(['admin', 'admin/dashboard']) ?>" href="<?= site_url('admin') ?>">
            <i class="fa-solid fa-gauge"></i><span>Dashboard</span>
        </a>

        <a class="admin-nav__link <?= $activeExact('admin/slot') ?>" href="<?= site_url('admin/slot') ?>">
            <i class="fa-solid fa-calendar-plus"></i><span>Kelola Slot</span>
        </a>

        <a class="admin-nav__link <?= $activeAny(['admin/profile', 'admin/profile_edit']) ?>"
            href="<?= site_url('admin/profile') ?>">
            <i class="fa-solid fa-user-gear"></i><span>Profil Admin</span>
        </a>

        <a class="admin-nav__link <?= $activeExact('admin/kerja') ?> <?= $activeStart('admin/kerja/') ?>"
            href="<?= site_url('admin/kerja') ?>">
            <i class="fa-solid fa-briefcase"></i><span>Halaman Kerja</span>
        </a>

        <!-- ===== PPAT ===== -->
        <details class="nav-group" <?= $isPPATOpen ? 'open' : '' ?>>
            <summary class="nav-group__summary">
                <i class="fa-solid fa-folder-open"></i><span>PPAT</span><i class="fa-solid fa-chevron-down caret"></i>
            </summary>
            <div class="nav-group__items">
                <?php if (!empty($menu['ppat'])): ?>
                    <?php foreach ($menu['ppat'] as $it): ?>
                        <?php $href = site_url('admin/kerja/ppat/' . $it['slug']); ?>
                        <a class="admin-nav__sublink <?= $activeExact('admin/kerja/ppat/' . $it['slug']) ?>"
                            href="<?= $href ?>">
                            <?= esc($it['title']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- fallback minimal biar tetap ada pintu masuk -->
                    <a class="admin-nav__sublink <?= $activeExact('admin/kerja/ppat') ?>"
                        href="<?= site_url('admin/kerja/ppat') ?>">
                        Buka Folder PPAT
                    </a>
                <?php endif; ?>
            </div>
        </details>

        <!-- ===== NOTARIS ===== -->
        <details class="nav-group" <?= $isNotarisOpen ? 'open' : '' ?>>
            <summary class="nav-group__summary">
                <i class="fa-solid fa-folder-open"></i><span>Notaris</span><i
                    class="fa-solid fa-chevron-down caret"></i>
            </summary>
            <div class="nav-group__items">
                <?php if (!empty($menu['notaris'])): ?>
                    <?php foreach ($menu['notaris'] as $it): ?>
                        <?php $href = site_url('admin/kerja/notaris/' . $it['slug']); ?>
                        <a class="admin-nav__sublink <?= $activeExact('admin/kerja/notaris/' . $it['slug']) ?>"
                            href="<?= $href ?>">
                            <?= esc($it['title']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a class="admin-nav__sublink <?= $activeExact('admin/kerja/notaris') ?>"
                        href="<?= site_url('admin/kerja/notaris') ?>">
                        Buka Folder Notaris
                    </a>
                <?php endif; ?>
            </div>
        </details>
    </nav>
</aside>