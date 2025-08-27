<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<body class="centered-page">
<main class="min-h-screen flex items-center justify-center px-4 py-8">
  <div class="glass-card rounded-3xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold text-black mb-6 text-center">Lupa Password</h2>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="mb-4 text-red-600"><?= esc(session('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mb-4 text-green-600"><?= esc(session('success')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('forgot') ?>" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-semibold text-black mb-2">Email</label>
        <input type="email" name="email" class="form-input w-full px-4 py-3 rounded-xl" required>
      </div>
      <button class="btn-primary w-full py-3 rounded-xl font-semibold text-black" type="submit">
        Kirim Tautan Reset
      </button>
      <p class="text-center text-sm mt-2">
        <a href="<?= base_url('login') ?>" class="text-blue-600">Kembali ke Login</a>
      </p>
    </form>
  </div>
</main>
</body>
<?= $this->endSection() ?>