<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<body class="centered-page">
    <main class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="glass-card rounded-3xl p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-black mb-6 text-center">Reset Password</h2>

            <?php if (session()->getFlashdata('errors')): ?>
                <pre class="mb-4 text-red-600 text-sm"><?= print_r(session('errors'), true) ?></pre>
            <?php endif; ?>

            <form method="post" action="<?= base_url('reset-password') ?>" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= esc($token) ?>">
                <div>
                    <label class="block text-sm font-semibold text-black mb-2">Password Baru</label>
                    <input type="password" name="password" class="form-input w-full px-4 py-3 rounded-xl" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-black mb-2">Ulangi Password</label>
                    <input type="password" name="password_confirm" class="form-input w-full px-4 py-3 rounded-xl"
                        required>
                </div>
                <button class="btn-primary w-full py-3 rounded-xl font-semibold text-black" type="submit">
                    Simpan Password
                </button>
            </form>
        </div>
    </main>
</body>
<?= $this->endSection() ?>