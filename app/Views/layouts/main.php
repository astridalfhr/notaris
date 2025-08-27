<?= $this->include('layouts/header') ?>
<?= $this->include('layouts/navbar') ?>
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