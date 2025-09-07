<?php
// Detect role (dengan fallback beberapa key session yang biasa dipakai)
$ses = session();
$role = strtolower(trim((string) ($ses->get('role') ?? $ses->get('user_role') ?? 'user')));

// Tentukan file navbar
$navbarView = ($role === 'admin') ? 'layouts/navbar_admin' : 'layouts/navbar';
?>

<?= $this->include('layouts/header') ?>
<?= $this->include($navbarView) ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert warn"><?= esc(session()->getFlashdata('warning')) ?></div>
<?php endif; ?>

<?= $this->renderSection('content') ?>
<?= $this->include('layouts/footer') ?>