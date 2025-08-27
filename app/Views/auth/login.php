<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<body class="centered-page">
    <div class="bg-animated"></div>

    <main class="min-h-screen flex items-center justify-center px-4 py-8">
        <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center main-container">
            <!-- Left side for introduction -->
            <section class="intro-section">
                <div class="mb-8">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-balance-scale text-black text-xl"></i>
                        </div>
                        <h1 class="text-5xl font-display font-bold text-black leading-tight">
                            Notaris Pelalawan
                        </h1>
                    </div>
                    <p class="text-xl text-black text-opacity-90 max-w-md leading-relaxed">
                        Akses mudah untuk semua kebutuhan layanan notaris Anda. Kelola dokumen, jadwal konsultasi, dan
                        layanan hukum dalam satu platform terpadu.
                    </p>
                </div>

                <!-- Feature Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div class="glass rounded-2xl p-6">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-file-contract text-black"></i>
                        </div>
                        <h3 class="text-black font-semibold mb-2">Draft Akta Digital</h3>
                        <p class="text-black text-opacity-80 text-sm">Buat dan kelola draft akta secara digital dengan
                            template terbaru</p>
                    </div>

                    <div class="glass rounded-2xl p-6">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-calendar-check text-black"></i>
                        </div>
                        <h3 class="text-black font-semibold mb-2">Jadwal Konsultasi</h3>
                        <p class="text-black text-opacity-80 text-sm">Atur jadwal konsultasi dengan sistem booking yang
                            mudah</p>
                    </div>
                </div>
            </section>

            <!-- Right side for login form -->
            <section class="flex justify-center lg:justify-end">
                <div class="glass-card rounded-3xl p-8 w-full max-w-md">
                    <div class="text-center mb-8">
                        <div
                            class="w-16 h-16 bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-shield text-black text-2xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-black mb-2">Selamat Datang Kembali</h2>
                        <p class="text-gray-600">Masuk ke akun notaris Anda</p>
                    </div>

                    <!-- Flash -->
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="mb-4 text-red-600"><?= esc(session('error')) ?></div>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('errors')): ?>
                        <pre class="mb-4 text-red-600 text-sm"><?= print_r(session('errors'), true) ?></pre>
                    <?php endif; ?>
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="mb-4 text-green-600"><?= esc(session('success')) ?></div>
                    <?php endif; ?>

                    <form id="login-form" class="space-y-6" autocomplete="off" method="post"
                        action="<?= base_url('auth/manual_login'); ?>">
                        <?= csrf_field() ?>

                        <!-- Google Login Button -->
                        <a href="<?= base_url('auth/LoginWithGoogle'); ?>"
                            class="btn-primary w-full py-4 px-6 rounded-xl font-semibold text-black flex items-center justify-center space-x-3">
                            <i class="fab fa-google text-lg"></i>
                            <span>Masuk dengan Google</span>
                        </a>

                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white text-gray-500">atau</span>
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-black mb-2">
                                <i class="fas fa-envelope mr-2 text-gray-400"></i>
                                Alamat Email
                            </label>
                            <input id="email" name="email" type="email" required placeholder="nama@email.com"
                                class="form-input w-full px-4 py-4 rounded-xl text-black placeholder-gray-400" />
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-semibold text-black mb-2">
                                <i class="fas fa-lock mr-2 text-gray-400"></i>
                                Password
                            </label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required
                                    placeholder="Masukkan password Anda"
                                    class="form-input w-full px-4 py-4 rounded-xl text-black placeholder-gray-400 pr-12" />
                                <button type="button" onclick="togglePassword()"
                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                            </label>
                            <a href="<?= site_url('forgot') ?>"
                                class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                                Lupa password?
                            </a>
                        </div>

                        <!-- Login Button -->
                        <button type="submit"
                            class="btn-primary w-full py-4 px-6 rounded-xl font-semibold text-black relative"
                            id="submit-button">
                            <span id="button-text">Masuk ke Notaris Pelalawan</span>
                            <span id="button-spinner" class="loading-spinner hidden"></span>
                        </button>

                        <!-- Footer -->
                        <p class="text-center text-sm text-gray-500">
                            Belum punya akun?
                            <a href="<?= base_url('register') ?>"
                                class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">Daftar
                                sekarang</a>
                        </p>
                    </form>

                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <p class="text-xs text-gray-400 text-center">
                            Dengan masuk, Anda menyetujui
                            <a href="#" class="text-blue-600 hover:underline">Kebijakan Privasi</a> dan
                            <a href="#" class="text-blue-600 hover:underline">Syarat Layanan</a> kami.
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
<?= $this->endSection() ?>