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
  case in_array($role, ['multi-user', 'multiuser', 'multiuser'], true):
    $dashboardUrl = 'multiuser/dashboard';
    $profileUrl = 'multiuser/profile_edit';
    break;
  default: // user atau tak dikenal
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
  $avatar = $photo; // URL Google
} elseif ($photo && is_file($localAbs)) {
  // cache-busting agar selalu ambil foto terbaru
  $ver = @filemtime($localAbs) ?: time();
  $avatar = base_url($localRel) . '?v=' . $ver;
}
?>

<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="container mx-auto flex justify-between items-center px-6 py-3">
    <div class="flex items-center space-x-10">
      <div class="text-xl font-bold text-gray-800 cursor-default select-none">Notaris</div>
      <!-- Navbar desktop -->
      <nav class="hidden md:flex space-x-8 text-gray-700 font-medium">
        <a href="<?= site_url('/') ?>" class="hover:text-yellow-600 transition">Beranda</a>
        <a href="<?= site_url('profile') ?>" class="hover:text-yellow-600 transition">Profile</a>
        <a href="<?= site_url('layanan') ?>" class="hover:text-yellow-600 transition">Layanan</a>
        <a href="<?= site_url('kontak') ?>" class="hover:text-yellow-600 transition">Kontak</a>
      </nav>
    </div>

    <!-- Bagian kanan desktop -->
    <div class="hidden md:flex space-x-6 items-center text-sm text-gray-600">
      <div>📞 +62 852-7128-8009</div>
      <div class="border-l border-gray-300 h-6"></div>

      <?php if ($isLogged): ?>
        <!-- Sudah login -->
        <div class="relative">
          <button id="userMenuButton" class="flex items-center space-x-2 hover:text-gray-900 focus:outline-none"
            aria-haspopup="true" aria-expanded="false">
            <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
              <?php if ($avatar): ?>
                <img src="<?= esc($avatar) ?>" alt="<?= esc($displayName ?: 'Me') ?>" class="w-full h-full object-cover">
              <?php else: ?>
                <i class="fas fa-user text-gray-600"></i>
              <?php endif; ?>
            </div>
            <span class="font-medium">
              <?= esc($displayName ?: $displayEmail ?: 'Akun') ?>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"
              aria-hidden="true">
              <path fill-rule="evenodd"
                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"
                clip-rule="evenodd" />
            </svg>
          </button>

          <!-- Dropdown -->
          <div id="userDropdown"
            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">
            <a href="<?= site_url($dashboardUrl) ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              Dashboard
            </a>
            <a href="<?= site_url($profileUrl) ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              Edit Profile
            </a>
            <div class="border-t border-gray-200 my-1"></div>
            <a href="<?= site_url('logout') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
              Logout
            </a>
          </div>
        </div>
      <?php else: ?>
        <!-- Belum login -->
        <a href="<?= site_url('login') ?>"
          class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
          Login / Sign Up
        </a>
      <?php endif; ?>
    </div>

    <!-- Hamburger menu button untuk mobile -->
    <div class="md:hidden flex items-center space-x-4">
      <!-- Nomor telepon bisa disembunyikan atau ditampilkan sesuai kebutuhan -->
      <button id="mobileMenuBtn" aria-label="Toggle menu" class="focus:outline-none">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>

      <?php if ($isLogged): ?>
        <!-- Tombol user icon di mobile -->
        <button id="mobileUser Btn" aria-label="User  menu" class="focus:outline-none">
          <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
            <?php if ($avatar): ?>
              <img src="<?= esc($avatar) ?>" alt="<?= esc($displayName ?: 'Me') ?>" class="w-full h-full object-cover">
            <?php else: ?>
              <i class="fas fa-user text-gray-600"></i>
            <?php endif; ?>
          </div>
        </button>
      <?php else: ?>
        <!-- Tombol login di mobile -->
        <a href="<?= site_url('login') ?>"
          class="px-3 py-1 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-sm">
          Login
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mobile menu (hidden by default) -->
  <nav id="mobileMenu" class="hidden md:hidden bg-white border-t border-gray-200 shadow-md">
    <div class="flex flex-col px-6 py-4 space-y-3 text-gray-700 font-medium">
      <a href="<?= site_url('/') ?>" class="hover:text-yellow-600 transition">Beranda</a>
      <a href="<?= site_url('profile') ?>" class="hover:text-yellow-600 transition">Profile</a>
      <a href="<?= site_url('layanan') ?>" class="hover:text-yellow-600 transition">Layanan</a>
      <a href="<?= site_url('kontak') ?>" class="hover:text-yellow-600 transition">Kontak</a>
      <div class="border-t border-gray-300 my-2"></div>
      <div class="text-sm text-gray-600">
        📞 +62 852-7128-8009
      </div>

      <?php if ($isLogged): ?>
        <!-- Dropdown user mobile -->
        <div class="border-t border-gray-300 pt-2">
          <a href="<?= site_url($dashboardUrl) ?>" class="block py-2 hover:text-yellow-600 transition">Dashboard</a>
          <a href="<?= site_url($profileUrl) ?>" class="block py-2 hover:text-yellow-600 transition">Edit Profile</a>
          <a href="<?= site_url('logout') ?>" class="block py-2 hover:text-yellow-600 transition">Logout</a>
        </div>
      <?php else: ?>
        <a href="<?= site_url('login') ?>"
          class="block py-2 px-4 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition text-center">
          Login / Sign Up
        </a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- Dropdown user mobile (popup) -->
  <div id="mobileUser Dropdown"
    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-11/12 max-w-sm p-6">
      <h2 class="text-xl font-semibold mb-4">Akun</h2>
      <div class="flex items-center space-x-4 mb-4">
        <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
          <?php if ($avatar): ?>
            <img src="<?= esc($avatar) ?>" alt="<?= esc($displayName ?: 'Me') ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <i class="fas fa-user text-gray-600"></i>
          <?php endif; ?>
        </div>
        <div>
          <div class="font-medium"><?= esc($displayName ?: $displayEmail ?: 'Akun') ?></div>
          <div class="text-sm text-gray-500"><?= esc($displayEmail) ?></div>
        </div>
      </div>
      <a href="<?= site_url($dashboardUrl) ?>" class="block py-2 hover:text-yellow-600 transition">Dashboard</a>
      <a href="<?= site_url($profileUrl) ?>" class="block py-2 hover:text-yellow-600 transition">Edit Profile</a>
      <div class="border-t border-gray-300 my-2"></div>
      <a href="<?= site_url('logout') ?>" class="block py-2 hover:text-yellow-600 transition">Logout</a>
      <button id="closeMobileUser Dropdown"
        class="mt-4 w-full py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
        Tutup
      </button>
    </div>
  </div>
</header>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Dropdown user desktop
    const userBtn = document.getElementById("userMenuButton");
    const userDropdown = document.getElementById("userDropdown");

    if (userBtn && userDropdown) {
      function closeUser Menu() {
        if (!userDropdown.classList.contains("hidden")) {
          userDropdown.classList.add("hidden");
          userBtn.setAttribute("aria-expanded", "false");
        }
      }

      userBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        const isHidden = userDropdown.classList.contains("hidden");
        userDropdown.classList.toggle("hidden");
        userBtn.setAttribute("aria-expanded", String(isHidden));
      });

      document.addEventListener("click", closeUser Menu);
      document.addEventListener("keydown", function (e) { if (e.key === "Escape") closeUser Menu(); });
    }

    // Toggle mobile menu
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");
    const mobileMenu = document.getElementById("mobileMenu");

    if (mobileMenuBtn && mobileMenu) {
      mobileMenuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
      });
    }

    // Toggle mobile user dropdown
    const mobileUser Btn = document.getElementById("mobileUser Btn");
    const mobileUser Dropdown = document.getElementById("mobileUser Dropdown");
    const closeMobileUser DropdownBtn = document.getElementById("closeMobileUser Dropdown");

    if (mobileUser Btn && mobileUser Dropdown && closeMobileUser DropdownBtn) {
      mobileUser Btn.addEventListener("click", () => {
        mobileUser Dropdown.classList.remove("hidden");
    });

      closeMobileUser DropdownBtn.addEventListener("click", () => {
        mobileUser Dropdown.classList.add("hidden");
    });

      // Klik di luar modal tutup modal
      mobileUser Dropdown.addEventListener("click", (e) => {
      if (e.target === mobileUser Dropdown) {
          mobileUser Dropdown.classList.add("hidden");
    }
  });
    }
  });
</script>