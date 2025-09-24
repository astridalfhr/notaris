<?php
// ==== DETEKSI LOGIN (dukung key baru & legacy) ====
$ses = session();
$isLogged = (bool) (
    $ses->get('id') || $ses->get('user_id') || $ses->get('logged_in') || $ses->get('isLoggedIn')
);

$displayName = $ses->get('nama') ?? $ses->get('user_name') ?? '';
$displayEmail = $ses->get('email') ?? $ses->get('user_email') ?? '';
$photo = $ses->get('profile_photo') ?? '';
$role = strtolower(trim((string) ($ses->get('role') ?? 'user')));

// Tentukan URL dashboard & profile berdasar role
switch (true) {
    case in_array($role, ['karyawan', 'pegawai', 'admin', 'employee', 'staff'], true):
        $dashboardUrl = 'admin/dashboard';
        $profileUrl = 'admin/profile_edit';
        break;
    case in_array($role, ['multi-user', 'multiuser'], true):
        $dashboardUrl = 'multiuser/dashboard';
        $profileUrl = 'multiuser/profile_edit';
        break;
    default:
        $dashboardUrl = 'user/dashboard';
        $profileUrl = 'user/edit_profile';
        break;
}

// Avatar (URL atau lokal)
$isUrl = $photo && filter_var($photo, FILTER_VALIDATE_URL);
$localRel = 'uploads/profiles/' . $photo;
$localAbs = FCPATH . $localRel;
$avatar = null;
if ($isUrl) {
    $avatar = $photo;
} elseif ($photo && is_file($localAbs)) {
    $ver = @filemtime($localAbs) ?: time();
    $avatar = base_url($localRel) . '?v=' . $ver;
}
// Nama pendek untuk chip mobile
$shortName = trim(explode(' ', $displayName ?: ($displayEmail ?: 'Akun'))[0]);
?>

<header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 sm:px-6 py-3">
        <div class="flex items-center justify-between">
            <!-- LEFT -->
            <div class="flex items-center gap-3">
                <button id="btnOpenDrawerAdmin" class="md:hidden p-2 rounded hover:bg-gray-100" aria-label="Buka menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="text-xl font-bold text-gray-800 select-none">Notaris</div>

                <nav class="hidden md:flex items-center gap-8 text-gray-700 ml-6">
                    <a href="<?= site_url('/') ?>" class="hover:text-amber-600 transition">Beranda</a>
                    <a href="<?= site_url('profile') ?>" class="hover:text-amber-600 transition">Profile</a>
                    <a href="<?= site_url('layanan') ?>" class="hover:text-amber-600 transition">Layanan</a>

                    <?php if (in_array($role, ['admin', 'pegawai', 'karyawan', 'staff'], true)): ?>
                        <div class="relative">
                            <button id="kelolaMenuButton"
                                class="flex items-center gap-1 hover:text-amber-600 focus:outline-none" aria-haspopup="true"
                                aria-expanded="false">
                                <span>Kelola</span>
                                <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div id="kelolaDropdown"
                                class="absolute left-0 top-full mt-0 w-56 bg-white border border-gray-200 rounded-lg shadow-md hidden z-50">
                                <a href="<?= site_url('admin/homepage') ?>"
                                    class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kelola Beranda</a>
                                <a href="<?= site_url('admin/pekerjaan') ?>"
                                    class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kelola Layanan</a>
                                <a href="<?= site_url('admin/company') ?>"
                                    class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kelola Profile Perusahaan</a>
                                <a href="<?= site_url('admin/employees') ?>"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Kelola Karyawan</a>
                                <a href="<?= site_url('admin/roles') ?>"
                                    class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Kelola Role</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- RIGHT -->
            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center text-sm text-gray-600 mr-2">
                    <span>📞 +62 852-7128-8009</span>
                </div>

                <?php if ($isLogged): ?>
                    <!-- Desktop: dropdown akun -->
                    <div class="relative hidden md:block">
                        <button id="userMenuButton" class="flex items-center gap-2 hover:text-gray-900 focus:outline-none"
                            aria-haspopup="true" aria-expanded="false">
                            <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                                <?php if ($avatar): ?>
                                    <img src="<?= esc($avatar) ?>" alt="<?= esc($displayName ?: 'Me') ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fas fa-user text-gray-600"></i>
                                <?php endif; ?>
                            </div>
                            <span class="font-medium"><?= esc($displayName ?: $displayEmail ?: 'Akun') ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="userDropdown"
                            class="absolute right-0 top-full mt-0 w-48 bg-white border border-gray-200 rounded-lg shadow-md hidden">
                            <a href="<?= site_url($profileUrl) ?>"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Edit Profile</a>
                            <a href="<?= site_url('logout') ?>"
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>

                    <!-- Mobile: chip akun selalu tampil di kanan -->
                    <a href="<?= site_url($dashboardUrl) ?>"
                        class="md:hidden flex items-center gap-2 px-3 py-1 rounded-full border border-gray-300 text-sm">
                        <div class="w-7 h-7 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                            <?php if ($avatar): ?>
                                <img src="<?= esc($avatar) ?>" alt="<?= esc($shortName) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i class="fas fa-user text-gray-600 text-sm"></i>
                            <?php endif; ?>
                        </div>
                        <span class="font-medium"><?= esc($shortName) ?></span>
                    </a>
                <?php else: ?>
                    <!-- Guest: Login selalu tampil di kanan (mobile & desktop) -->
                    <a href="<?= site_url('login') ?>"
                        class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition text-sm">
                        Login / Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Drawer Mobile (Admin) -->
    <div id="mobileDrawerAdmin" class="fixed inset-0 z-50 hidden">
        <div id="drawerBackdropAdmin" class="absolute inset-0 bg-black/40"></div>
        <aside class="absolute left-0 top-0 h-full w-80 max-w-[86%] bg-white shadow-xl p-4 overflow-y-auto">
            <div class="flex items-center justify-between mb-3">
                <div class="text-lg font-semibold">Notaris</div>
                <button id="btnCloseDrawerAdmin" class="p-2 rounded hover:bg-gray-100" aria-label="Tutup menu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <?php if ($isLogged): ?>
                <div class="flex items-center gap-3 p-3 border rounded-lg mb-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                        <?php if ($avatar): ?>
                            <img src="<?= esc($avatar) ?>" alt="<?= esc($displayName ?: 'Me') ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-user text-gray-600"></i>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium truncate"><?= esc($displayName ?: $displayEmail ?: 'Akun') ?></div>
                        <div class="text-xs text-gray-500 truncate"><?= esc($displayEmail) ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <nav class="space-y-1">
                <a href="<?= site_url('/') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Beranda</a>
                <a href="<?= site_url('profile') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Profile</a>
                <a href="<?= site_url('layanan') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Layanan</a>

                <?php if (in_array($role, ['admin', 'pegawai', 'karyawan', 'staff'], true)): ?>
                    <div class="mt-2 mb-1 text-xs uppercase tracking-wide text-gray-500 px-3">Kelola</div>
                    <a href="<?= site_url('admin/homepage') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kelola
                        Beranda</a>
                    <a href="<?= site_url('admin/company') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kelola
                        Profile Perusahaan</a>
                    <a href="<?= site_url('admin/employees') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kelola
                        Karyawan</a>
                    <a href="<?= site_url('admin/roles') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kelola
                        Role</a>
                <?php endif; ?>
            </nav>

            <div class="mt-4 border-t pt-3 text-sm text-gray-600">
                📞 +62 852-7128-8009
            </div>

            <?php if ($isLogged): ?>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="<?= site_url($dashboardUrl) ?>"
                        class="px-3 py-2 text-center rounded border hover:bg-gray-50">Dashboard</a>
                    <a href="<?= site_url($profileUrl) ?>"
                        class="px-3 py-2 text-center rounded border hover:bg-gray-50">Edit Profile</a>
                    <a href="<?= site_url('logout') ?>"
                        class="col-span-2 px-3 py-2 text-center rounded bg-red-600 text-white hover:bg-red-700">Logout</a>
                </div>
            <?php else: ?>
                <a href="<?= site_url('login') ?>"
                    class="mt-4 block w-full text-center px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600">
                    Login / Sign Up
                </a>
            <?php endif; ?>
        </aside>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Drawer admin
        const d = document.getElementById('mobileDrawerAdmin');
        const op = document.getElementById('btnOpenDrawerAdmin');
        const cl = document.getElementById('btnCloseDrawerAdmin');
        const bk = document.getElementById('drawerBackdropAdmin');
        function open() { d?.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
        function close() { d?.classList.add('hidden'); document.body.style.overflow = ''; }
        op?.addEventListener('click', open);
        cl?.addEventListener('click', close);
        bk?.addEventListener('click', close);

        // User dropdown (desktop)
        const uBtn = document.getElementById('userMenuButton');
        const uDD = document.getElementById('userDropdown');
        function uClose() { if (uDD && !uDD.classList.contains('hidden')) { uDD.classList.add('hidden'); uBtn?.setAttribute('aria-expanded', 'false'); } }
        if (uBtn && uDD) {
            uBtn.addEventListener('click', (e) => { e.stopPropagation(); const willOpen = uDD.classList.contains('hidden'); uDD.classList.toggle('hidden'); uBtn.setAttribute('aria-expanded', String(willOpen)); });
            document.addEventListener('click', uClose);
            document.addEventListener('keydown', e => { if (e.key === 'Escape') uClose(); });
        }

        // Kelola dropdown
        const kBtn = document.getElementById('kelolaMenuButton');
        const kDD = document.getElementById('kelolaDropdown');
        function kClose() { if (kDD && !kDD.classList.contains('hidden')) { kDD.classList.add('hidden'); kBtn?.setAttribute('aria-expanded', 'false'); } }
        if (kBtn && kDD) {
            kBtn.addEventListener('click', (e) => { e.stopPropagation(); const willOpen = kDD.classList.contains('hidden'); kDD.classList.toggle('hidden'); kBtn.setAttribute('aria-expanded', String(willOpen)); });
            document.addEventListener('click', kClose);
            document.addEventListener('keydown', e => { if (e.key === 'Escape') kClose(); });
        }
    });
</script>