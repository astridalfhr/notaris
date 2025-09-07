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

// Avatar bisa URL (Google) atau file lokal /uploads/profiles/{filename}
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
?>

<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="container mx-auto px-4 sm:px-6 py-3">
    <div class="flex items-center justify-between">
      <!-- LEFT: Logo + hamburger (mobile) -->
      <div class="flex items-center gap-3">
        <button id="btnOpenDrawer" class="md:hidden p-2 rounded hover:bg-gray-100" aria-label="Buka menu">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div class="text-xl font-bold text-gray-800 select-none">Notaris</div>
        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-8 text-gray-700 font-medium ml-6">
          <a href="<?= site_url('/') ?>" class="hover:text-amber-600 transition">Beranda</a>
          <a href="<?= site_url('profile') ?>" class="hover:text-amber-600 transition">Profile</a>
          <a href="<?= site_url('layanan') ?>" class="hover:text-amber-600 transition">Layanan</a>
          <a href="<?= site_url('kontak') ?>" class="hover:text-amber-600 transition">Kontak</a>
        </nav>
      </div>

      <!-- RIGHT: phone + login/user -->
      <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center text-sm text-gray-600 mr-2">
          <span>📞 +62 852-7128-8009</span>
        </div>

        <?php if ($isLogged): ?>
          <!-- Avatar kiri, nama kanan -->
          <div class="relative hidden md:block">
            <button id="userMenuButton" class="flex items-center gap-2 hover:text-gray-900 focus:outline-none"
              aria-haspopup="true" aria-expanded="false">
              <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                <?php if ($avatar): ?>
                  <img src="<?= esc($avatar) ?>" alt="<?= esc($displayName ?: 'Me') ?>" class="w-full h-full object-cover">
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
              class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden">
              <a href="<?= site_url($dashboardUrl) ?>"
                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Dashboard</a>
              <a href="<?= site_url($profileUrl) ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Edit
                Profile</a>
              <div class="border-t my-1"></div>
              <a href="<?= site_url('logout') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <!-- Login tetap di kanan (mobile & desktop) -->
          <a href="<?= site_url('login') ?>"
            class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition text-sm">
            Login / Sign Up
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Drawer Mobile -->
  <div id="mobileDrawer" class="fixed inset-0 z-50 hidden">
    <div id="drawerBackdrop" class="absolute inset-0 bg-black/40"></div>
    <aside class="absolute left-0 top-0 h-full w-80 max-w-[86%] bg-white shadow-xl p-4 overflow-y-auto">
      <div class="flex items-center justify-between mb-3">
        <div class="text-lg font-semibold">Notaris</div>
        <button id="btnCloseDrawer" class="p-2 rounded hover:bg-gray-100" aria-label="Tutup menu">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <?php if ($isLogged): ?>
        <!-- Card akun: avatar di kiri, nama di kanan -->
        <div class="flex items-center gap-3 p-3 border rounded-lg mb-3">
          <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
            <?php if ($avatar): ?>
              <img src="<?= esc($avatar) ?>" alt="<?= esc($displayName ?: 'Me') ?>" class="w-full h-full object-cover">
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
        <a href="<?= site_url('kontak') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50">Kontak</a>
      </nav>

      <div class="mt-4 border-t pt-3 text-sm text-gray-600">
        📞 +62 852-7128-8009
      </div>

      <?php if ($isLogged): ?>
        <div class="mt-4 grid grid-cols-2 gap-2">
          <a href="<?= site_url($dashboardUrl) ?>"
            class="px-3 py-2 text-center rounded border hover:bg-gray-50">Dashboard</a>
          <a href="<?= site_url($profileUrl) ?>" class="px-3 py-2 text-center rounded border hover:bg-gray-50">Edit
            Profile</a>
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
    const drawer = document.getElementById('mobileDrawer');
    const openBtn = document.getElementById('btnOpenDrawer');
    const closeBtn = document.getElementById('btnCloseDrawer');
    const backdrop = document.getElementById('drawerBackdrop');

    function open() { drawer?.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function close() { drawer?.classList.add('hidden'); document.body.style.overflow = ''; }

    openBtn?.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    backdrop?.addEventListener('click', close);

    // user dropdown (desktop)
    const uBtn = document.getElementById('userMenuButton');
    const uDD = document.getElementById('userDropdown');
    function uClose() { if (uDD && !uDD.classList.contains('hidden')) { uDD.classList.add('hidden'); uBtn?.setAttribute('aria-expanded', 'false'); } }
    if (uBtn && uDD) {
      uBtn.addEventListener('click', (e) => { e.stopPropagation(); const willOpen = uDD.classList.contains('hidden'); uDD.classList.toggle('hidden'); uBtn.setAttribute('aria-expanded', String(willOpen)); });
      document.addEventListener('click', uClose);
      document.addEventListener('keydown', e => { if (e.key === 'Escape') uClose(); });
    }
  });
</script>