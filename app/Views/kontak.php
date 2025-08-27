<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Hubungi Kami - Kantor Notaris') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box
    }

    .page-contact {
      background: #f9fafc;
      font-family: 'Inter', sans-serif;
      color: #222;
      min-height: 100vh;
      padding: 0
    }

    /* Header */
    .navbar {
      background: #fff;
      padding: 1rem 0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .1)
    }

    .navbar .container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 2rem
    }

    .navbar .nav-links {
      display: flex;
      list-style: none;
      gap: 2rem
    }

    .navbar .nav-links a {
      text-decoration: none;
      color: #222;
      font-weight: 500;
      transition: color .3s
    }

    .navbar .nav-links a:hover,
    .navbar .nav-links a.active {
      color: #d97706
    }

    .navbar .login-btn {
      background: #fbbf24;
      color: #202020;
      padding: .5rem 1.5rem;
      border-radius: .5rem;
      text-decoration: none;
      font-weight: 600;
      transition: background-color .3s
    }

    .navbar .login-btn:hover {
      background: #d97706;
      color: #fff
    }

    /* Main */
    .main-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 3rem 2rem
    }

    .page-header {
      text-align: left;
      margin-bottom: 3rem
    }

    .page-header .icon-title {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem
    }

    .page-header .title-icon {
      font-size: 2rem
    }

    .page-header h1 {
      font-size: 2.5rem;
      font-weight: 700;
      color: #222
    }

    .page-header p {
      font-size: 1.1rem;
      color: #6b7280;
      margin-top: .5rem;
      max-width: 600px
    }

    /* Grid */
    .contact-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      align-items: start
    }

    /* Left */
    .services-section {
      background: #fff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1)
    }

    .services-grid {
      display: grid;
      gap: 1.5rem
    }

    .service-card {
      background: #fffbea;
      padding: 1.5rem;
      border-radius: .75rem;
      border-left: 4px solid #fbbf24;
      transition: all .3s
    }

    .service-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(251, 191, 36, .15)
    }

    .service-card .service-icon {
      font-size: 1.5rem;
      margin-bottom: .5rem
    }

    .service-card h3 {
      font-size: 1.125rem;
      font-weight: 600;
      color: #222;
      margin-bottom: .5rem
    }

    .service-card p {
      font-size: .9rem;
      color: #6b7280;
      line-height: 1.5
    }

    /* Contact info cards */
    .contact-info-grid {
      display: grid;
      gap: 1rem;
      margin-top: 2rem
    }

    .contact-info-card {
      background: #f8fafc;
      padding: 1rem;
      border-radius: .75rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: all .3s;
      border: 2px solid transparent;
      text-decoration: none;
      color: inherit
    }

    .contact-info-card:hover {
      background: #fffbea;
      border-color: #fbbf24;
      transform: translateX(5px)
    }

    .contact-info-card .info-icon {
      width: 2.5rem;
      height: 2.5rem;
      background: #fbbf24;
      border-radius: .5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      flex-shrink: 0
    }

    .contact-info-card .info-content h4 {
      font-size: .9rem;
      font-weight: 600;
      color: #222;
      margin-bottom: .25rem
    }

    .contact-info-card .info-content p {
      font-size: .85rem;
      color: #6b7280
    }

    /* Right (form) */
    .contact-form-section {
      background: #fff;
      padding: 2.5rem;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, .1);
      position: relative
    }

    .form-header {
      text-align: center;
      margin-bottom: 2rem
    }

    .icon-bg {
      width: 4rem;
      height: 4rem;
      background: #fbbf24;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 1.5rem
    }

    .form-header h2 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #222;
      margin-bottom: .5rem
    }

    .form-header p {
      color: #6b7280;
      font-size: .95rem
    }

    .form-group {
      margin-bottom: 1.5rem
    }

    .form-group label {
      display: block;
      font-weight: 600;
      color: #222;
      margin-bottom: .5rem;
      font-size: .9rem
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: .875rem 1rem;
      border: 2px solid #e5e7eb;
      border-radius: .5rem;
      font-size: 1rem;
      font-family: 'Inter', sans-serif;
      transition: all .3s;
      background: #fff
    }

    .input-focus:focus {
      outline: none !important;
      border-color: #d97706;
      box-shadow: 0 0 0 3px rgb(253 186 116 / .5)
    }

    .form-group textarea {
      min-height: 120px;
      resize: vertical;
      transition: background-color .3s, box-shadow .3s
    }

    .form-group textarea:focus {
      background: #fffbea;
      box-shadow: 0 0 8px 2px rgba(251, 191, 36, .5)
    }

    .btn-primary {
      background: #fbbf24;
      color: #202020;
      font-weight: 600;
      padding: 1rem 2rem;
      border: none;
      border-radius: .5rem;
      font-size: 1rem;
      cursor: pointer;
      transition: all .3s;
      width: 100%;
      position: relative;
      overflow: hidden
    }

    .btn-primary:hover {
      background: #d97706;
      color: #fff;
      transform: translateY(-1px)
    }

    .btn-submit:after {
      content: "→";
      position: absolute;
      right: 1.25rem;
      top: 50%;
      transform: translateY(-50%) translateX(0);
      opacity: 0;
      transition: opacity .3s, transform .3s
    }

    .btn-submit:hover:after {
      opacity: 1;
      transform: translateY(-50%) translateX(5px)
    }

    .success-message {
      background: linear-gradient(135deg, #fbbf24, #d97706);
      color: #202020;
      padding: 1rem;
      border-radius: .5rem;
      margin-bottom: 1.5rem;
      font-weight: 600;
      animation: slideDown .5s ease-out
    }

    .error-message {
      background: #fee2e2;
      color: #7f1d1d;
      padding: 1rem;
      border-radius: .5rem;
      margin-bottom: 1.5rem;
      font-weight: 600;
      animation: slideDown .5s ease-out
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px)
      }

      to {
        opacity: 1;
        transform: translateY(0)
      }
    }

    @media (max-width:768px) {
      .navbar .container {
        padding: 0 1rem
      }

      .navbar .nav-links {
        display: none
      }

      .main-container {
        padding: 2rem 1rem
      }

      .contact-layout {
        grid-template-columns: 1fr;
        gap: 2rem
      }

      .page-header h1 {
        font-size: 2rem
      }

      .contact-form-section,
      .services-section {
        padding: 1.5rem
      }
    }
  </style>
</head>
<body class="page-contact">
  <!-- MAIN -->
  <div class="main-container">
    <div class="page-header">
      <div class="icon-title">
        <span class="title-icon">⚖️</span>
        <h1>Notaris Pelalawan</h1>
      </div>
      <p>Akses mudah untuk semua kebutuhan layanan notaris Anda. Kelola dokumen, jadwal konsultasi, dan layanan hukum
        dalam satu platform terpadu.</p>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
      <div class="success-message"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
      <div class="error-message"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="contact-layout">
      <!-- KIRI -->
      <div class="services-section">
        <div class="services-grid">
          <div class="service-card">
            <div class="service-icon">📁</div>
            <h3>Draft Akta Digital</h3>
            <p>Buat dan kelola draft akta secara digital dengan template terbaru</p>
          </div>
          <div class="service-card">
            <div class="service-icon">📅</div>
            <h3>Jadwal Konsultasi</h3>
            <p>Atur jadwal konsultasi dengan sistem booking yang mudah</p>
          </div>
        </div>

        <!-- CONTACT INFO (LINKS) -->
        <div class="contact-info-grid">
          <a class="contact-info-card" href="<?= esc($waLink) ?>" target="_blank" rel="noopener">
            <div class="info-icon">📞</div>
            <div class="info-content">
              <h4>Telepon & WhatsApp</h4>
              <p><?= esc($hotline) ?></p>
            </div>
          </a>

          <a class="contact-info-card" href="<?= esc($mailtoLink) ?>">
            <div class="info-icon">📧</div>
            <div class="info-content">
              <h4>Email</h4>
              <p><?= esc($notarisEmail) ?></p>
            </div>
          </a>

          <a class="contact-info-card" href="<?= esc($mapsLink) ?>" target="_blank" rel="noopener">
            <div class="info-icon">📍</div>
            <div class="info-content">
              <h4>Alamat Kantor</h4>
              <p><?= esc($address) ?></p>
            </div>
          </a>

          <a class="contact-info-card" href="<?= esc($layananUrl) ?>">
            <div class="info-icon">🕒</div>
            <div class="info-content">
              <h4>Jam Operasional</h4>
              <p>Senin - Sabtu: 08:00 - 17:00</p>
            </div>
          </a>
        </div>
      </div>

      <!-- KANAN: FORM -->
      <div class="contact-form-section">
        <div class="form-header">
          <div class="icon-bg">👤</div>
          <h2>Hubungi Notaris Pelalawan</h2>
          <p>Kami siap membantu Anda. Silakan isi form di bawah ini atau hubungi kami langsung.</p>
        </div>

        <form method="post" action="<?= site_url('contact/send') ?>" autocomplete="off" novalidate>
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" class="input-focus" placeholder="Masukkan nama lengkap Anda"
              value="<?= old('name') ?>" required>
            <?php if (!empty($errors['name'])): ?>
              <div class="error-message" style="margin-top:.5rem"><?= esc($errors['name']) ?></div><?php endif; ?>
          </div>

          <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" id="email" name="email" class="input-focus" placeholder="contoh@email.com"
              value="<?= old('email') ?>" required>
            <?php if (!empty($errors['email'])): ?>
              <div class="error-message" style="margin-top:.5rem"><?= esc($errors['email']) ?></div><?php endif; ?>
          </div>

          <div class="form-group">
            <label for="phone">Nomor Telepon</label>
            <input type="tel" id="phone" name="phone" class="input-focus" placeholder="+62xxxxxxxxxx"
              value="<?= old('phone') ?>" required>
            <?php if (!empty($errors['phone'])): ?>
              <div class="error-message" style="margin-top:.5rem"><?= esc($errors['phone']) ?></div><?php endif; ?>
          </div>

          <div class="form-group">
            <label for="service">Jenis Layanan</label>
            <select id="service" name="service" class="input-focus" required>
              <option value="">Pilih Layanan</option>
              <option value="Jual Beli Tanah & Bangunan" <?= old('service') === 'Jual Beli Tanah & Bangunan' ? 'selected' : ''; ?>>Jual Beli Tanah & Bangunan</option>
              <option value="Pendirian Perusahaan" <?= old('service') === 'Pendirian Perusahaan' ? 'selected' : ''; ?>>
                Pendirian Perusahaan</option>
              <option value="Hibah" <?= old('service') === 'Hibah' ? 'selected' : ''; ?>>Hibah</option>
              <option value="Pembagian Waris" <?= old('service') === 'Pembagian Waris' ? 'selected' : ''; ?>>Pembagian Waris
              </option>
              <option value="Perjanjian Kawin" <?= old('service') === 'Perjanjian Kawin' ? 'selected' : ''; ?>>Perjanjian Kawin
              </option>
              <option value="Legalisasi Dokumen" <?= old('service') === 'Legalisasi Dokumen' ? 'selected' : ''; ?>>Legalisasi
                Dokumen</option>
              <option value="Konsultasi Hukum" <?= old('service') === 'Konsultasi Hukum' ? 'selected' : ''; ?>>Konsultasi Hukum
              </option>
              <option value="Lainnya" <?= old('service') === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
            </select>
          </div>

          <div class="form-group">
            <label for="message">Pesan / Keterangan</label>
            <textarea id="message" name="message" class="input-focus"
              placeholder="Jelaskan kebutuhan Anda secara detail..." required><?= old('message') ?></textarea>
            <?php if (!empty($errors['message'])): ?>
              <div class="error-message" style="margin-top:.5rem"><?= esc($errors['message']) ?></div><?php endif; ?>
          </div>

          <button type="submit" class="btn-primary btn-submit">Kirim Pesan</button>
        </form>
      </div>
    </div>
  </div>
</body>
<?= $this->endSection() ?>