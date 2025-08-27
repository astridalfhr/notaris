<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Diperlukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-2xl p-8 max-w-md text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Login Diperlukan</h2>
        <p class="text-gray-600 mb-6">
            Untuk melakukan booking, silakan login terlebih dahulu.
        </p>
        <a href="<?= site_url('login') ?>" 
           class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
            Login Sekarang
        </a>
    </div>
</body>
</html>
