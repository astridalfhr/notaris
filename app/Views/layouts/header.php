<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <!-- ✅ Viewport wajib untuk responsif -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">

  <!-- ✅ Title dinamis -->
  <title><?= esc($title ?? 'Notaris') ?></title>

  <!-- ✅ Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- ✅ Custom CSS -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

  <!-- ✅ Font Awesome (cukup satu versi terbaru saja, biar gak duplicate) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- ✅ Google Sign-In -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>

  <!-- ✅ Favicon (optional biar lebih pro) -->
  <link rel="icon" href="<?= base_url('assets/images/favicon.ico') ?>" type="image/x-icon">
</head>

<body class="bg-gray-50 font-sans text-gray-900 antialiased leading-relaxed">