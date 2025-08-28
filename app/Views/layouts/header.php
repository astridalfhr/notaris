<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />

  <!-- ✅ Responsif & safe area -->
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

  <!-- ✅ Warna bar address di Android (opsional) -->
  <meta name="theme-color" content="#0ea5e9" />

  <!-- ✅ SEO dasar -->
  <meta name="description"
    content="<?= esc($metaDescription ?? 'Layanan notaris — konsultasi, pembuatan akta, legalisasi, dan lainnya.') ?>" />

  <!-- ✅ Title dinamis -->
  <title><?= esc($title ?? 'Notaris') ?></title>

  <!-- ✅ Favicon & ikon perangkat (sesuaikan jika punya PNG) -->
  <link rel="icon" href="<?= base_url('assets/images/favicon.ico') ?>" type="image/x-icon" />
  <link rel="apple-touch-icon" href="<?= base_url('assets/images/icon-180.png') ?>" sizes="180x180" />

  <!-- ✅ Preconnect untuk CDN (sedikit bantu first paint) -->
  <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin />
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin />

  <!-- ✅ Tailwind CSS CDN + konfigurasi ringan -->
  <script>
    // Konfig cepat: container center, screens, dan warna brand
    window.tailwind = {
      theme: {
        extend: {
          colors: { brand: '#0ea5e9' }
        },
        container: { center: true, padding: '1rem' },
        screens: { sm: '640px', md: '768px', lg: '1024px', xl: '1280px' }
      },
      corePlugins: {
        // aktifkan utilitas penting
        container: true
      }
    };
  </script>
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- ✅ Custom CSS kamu -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>" />
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=6') ?>">

  <!-- ✅ Font Awesome (pastikan hanya satu versi) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    integrity="sha512-1ycn6IcaQQ40/J6MdLecxB2FSWbYQd1Y15w7qvcu+oVQm8bX9rQx0vKYLbB+X9Xh8GKa9r3G5k3L1j4nY6J0A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    integrity="sha512-dymI62VZ9rKqgW7o0dXz1Oj0ZpNY0s5U1yW9rQHcZC5f2KOSgQ0V6o1kC5kG1dXHkDvYhN8N5Zrd1ZC2M1iQ3g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Tambah: v4-shims supaya kelas lama seperti `fa fa-users` tetap muncul -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/v4-shims.min.css"
    integrity="sha512-vV6D4XYw7m3g8q8QXwCkq8d7lK/1o0uQJt8dEg9ZrCkq8xw2mQ0Lk3fG9sPZbQoq5d0G6mKq3QS2m3lM0l2VhA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />


  <!-- ✅ Google Sign-In -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body class="bg-gray-50 text-gray-900 antialiased leading-relaxed">