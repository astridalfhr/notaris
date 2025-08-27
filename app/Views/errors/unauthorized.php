<!-- app/Views/errors/unauthorized.php -->
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>403 – Akses Ditolak</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{margin:0;background:#f6f7fb;font-family:system-ui,Arial,sans-serif}
    .card{max-width:680px;margin:12vh auto;background:#fff;border-radius:14px;padding:28px;
          box-shadow:0 10px 30px rgba(0,0,0,.06);text-align:center}
    h1,h2{margin:0 0 8px}
    p{color:#6b7280}
    a{display:inline-block;margin-top:16px;padding:10px 14px;border-radius:10px;background:#111827;color:#fff;text-decoration:none}
  </style>
</head>
<body>
  <div class="card">
    <h2>403 – Akses Ditolak</h2>
    <p>Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="<?= site_url('/') ?>">Kembali ke Beranda</a>
  </div>
</body>
</html>
