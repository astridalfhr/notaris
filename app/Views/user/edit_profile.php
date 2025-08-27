<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="dashboard-container">
  <div class="section" style="max-width:720px;margin:0 auto;">
    <h2 class="section-title"><i class="fas fa-user-cog"></i> Edit Profil</h2>

    <?php if (session('error')): ?>
      <div class="glass-card" style="padding:12px;margin-bottom:12px;color:#721c24;background:#f8d7da;border:1px solid #f5c2c7;">
        <?= esc(session('error')) ?>
      </div>
    <?php endif; ?>
    <?php if (session('errors')): $errs = (array) session('errors'); ?>
      <div class="glass-card" style="padding:12px;margin-bottom:12px;color:#721c24;background:#f8d7da;border:1px solid #f5c2c7;">
        <?php foreach ($errs as $e): ?>
          <div><?= esc($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if (session('success')): ?>
      <div class="glass-card" style="padding:12px;margin-bottom:12px;color:#0f5132;background:#d1e7dd;border:1px solid #badbcc;">
        <?= esc(session('success')) ?>
      </div>
    <?php endif; ?>

    <?php
      $photo = $user['profile_photo'] ?? '';
      $isUrl = filter_var($photo, FILTER_VALIDATE_URL);
      $localUrl = $photo && !$isUrl ? base_url('uploads/profiles/'.$photo) : '';
      $currentSrc = $isUrl ? $photo : ($localUrl ?: '');
    ?>

    <form action="<?= base_url('user/edit_profile') ?>" method="post" enctype="multipart/form-data" class="booking-form">
      <?= csrf_field() ?>

      <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;">
        <div class="profile-avatar">
          <?php if ($currentSrc): ?>
            <img src="<?= esc($currentSrc) ?>" class="avatar-image" alt="Profile Photo">
          <?php else: ?>
            <div class="default-avatar"><i class="fas fa-user"></i></div>
          <?php endif; ?>
        </div>
        <div>
          <label class="form-group" style="display:block;">
            <span style="display:block;margin-bottom:6px;font-weight:600;">Foto Profil</span>
            <input type="file" name="photo" accept="image/*" class="form-input">
          </label>
          <label style="display:flex;align-items:center;gap:8px;margin-top:8px;">
            <input type="checkbox" name="remove_photo" value="1">
            <span>Hapus foto saat ini</span>
          </label>
        </div>
      </div>

      <div class="form-group">
        <label>Nama</label>
        <input type="text" name="nama" class="form-input" value="<?= esc($user['nama'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-input" value="<?= esc($user['email'] ?? '') ?>">
      </div>

      <button type="submit" class="booking-btn" style="width:100%;text-align:center;">
        <i class="fas fa-save"></i> Simpan Perubahan
      </button>
    </form>

    <div style="margin-top:12px;">
      <a href="<?= base_url('user/dashboard') ?>" class="arrow-btn">&larr; Kembali ke Dashboard</a>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<?= $this->endSection() ?>
