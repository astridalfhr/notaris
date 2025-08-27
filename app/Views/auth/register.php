<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<body class="centered-page">
<main class="min-h-screen flex items-center justify-center px-4 py-8">
  <div class="glass-card rounded-3xl p-8 w-full max-w-md">
    <div class="text-center mb-6">
      <div class="w-16 h-16 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-user-plus text-black text-2xl"></i>
      </div>
      <h2 class="text-2xl font-bold text-black">Buat Akun Baru</h2>
      <p class="text-gray-600 text-sm">Daftar untuk mengakses layanan Notaris Pelalawan</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="mb-4 text-green-600"><?= esc(session('success')) ?></div>
    <?php endif; ?>

    <?php if ($errs = session()->getFlashdata('errors')): ?>
      <ul class="mb-4 text-red-600 text-sm list-disc list-inside space-y-1">
        <?php foreach ((array)$errs as $e): ?>
          <li><?= esc($e) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <form method="post" action="<?= base_url('auth/register') ?>" class="space-y-4" autocomplete="off">
      <?= csrf_field() ?>

      <!-- Nama -->
      <div>
        <label class="block text-sm font-semibold text-black mb-2">
          <i class="fas fa-id-card mr-2 text-gray-400"></i> Nama Lengkap
        </label>
        <input type="text" name="nama" value="<?= old('nama') ?>"
               class="form-input w-full px-4 py-3 rounded-xl text-black placeholder-gray-400"
               placeholder="Nama lengkap" required>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-semibold text-black mb-2">
          <i class="fas fa-envelope mr-2 text-gray-400"></i> Email
        </label>
        <input type="email" name="email" value="<?= old('email') ?>"
               class="form-input w-full px-4 py-3 rounded-xl text-black placeholder-gray-400"
               placeholder="nama@email.com" required>
      </div>

      <!-- Password -->
      <div>
        <label class="block text-sm font-semibold text-black mb-2">
          <i class="fas fa-lock mr-2 text-gray-400"></i> Password
        </label>
        <div class="relative">
          <input id="password" type="password" name="password"
                 class="form-input w-full px-4 py-3 rounded-xl text-black placeholder-gray-400 pr-12"
                 placeholder="Minimal 6 karakter" minlength="6" required>
          <button type="button" onclick="togglePw('password','pw-icon')"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <i id="pw-icon" class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <!-- Konfirmasi Password -->
      <div>
        <label class="block text-sm font-semibold text-black mb-2">
          <i class="fas fa-check-circle mr-2 text-gray-400"></i> Konfirmasi Password
        </label>
        <div class="relative">
          <input id="password_confirm" type="password" name="password_confirm"
                 class="form-input w-full px-4 py-3 rounded-xl text-black placeholder-gray-400 pr-12"
                 placeholder="Ulangi password" minlength="6" required>
          <button type="button" onclick="togglePw('password_confirm','pw2-icon')"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
            <i id="pw2-icon" class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <!-- Opsional: persetujuan -->
      <label class="flex items-start gap-2 text-sm text-gray-600">
        <input type="checkbox" class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
        <span>
          Saya menyetujui <a href="#" class="text-blue-600 hover:underline">Syarat Layanan</a> dan
          <a href="#" class="text-blue-600 hover:underline">Kebijakan Privasi</a>.
        </span>
      </label>

      <!-- Submit -->
      <button class="btn-primary w-full py-3 rounded-xl font-semibold text-black" type="submit">
        Daftar
      </button>

      <!-- Footer link -->
      <p class="text-center text-sm text-gray-500">
        Sudah punya akun?
        <a href="<?= base_url('login') ?>" class="text-blue-600 hover:text-blue-800 font-semibold">Masuk</a>
      </p>

      <!-- Opsional: daftar cepat dengan Google -->
      <div class="relative my-2">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
        <div class="relative flex justify-center">
          <span class="px-3 bg-white text-gray-400 text-xs">atau</span>
        </div>
      </div>
      <a href="<?= base_url('auth/LoginWithGoogle') ?>"
         class="btn-primary w-full py-3 rounded-xl font-semibold text-black flex items-center justify-center gap-3">
        <i class="fab fa-google"></i> Daftar dengan Google
      </a>
    </form>
  </div>
</main>

<script>
  function togglePw(inputId, iconId) {
    const el = document.getElementById(inputId);
    const ic = document.getElementById(iconId);
    if (el.type === 'password') { el.type = 'text'; ic.classList.replace('fa-eye','fa-eye-slash'); }
    else { el.type = 'password'; ic.classList.replace('fa-eye-slash','fa-eye'); }
  }
</script>
</body>

<?= $this->endSection() ?>
