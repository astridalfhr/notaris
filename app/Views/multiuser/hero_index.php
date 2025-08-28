<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-images"></i> Hero / Banner</h2>
            <a class="btn success" href="<?= site_url('multiuser/hero/create') ?>"
                style="background:#16A34A;color:#fff;border:none;padding:10px 14px;border-radius:10px;text-decoration:none;">+
                Tambah Banner</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="card" style="border-left:4px solid #10B981;"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php elseif (session()->getFlashdata('error')): ?>
            <div class="card" style="border-left:4px solid #EF4444;"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3 class="card-title"><i class="fa-solid fa-list"></i> Daftar Banner</h3>
            <div class="table-responsive">
                <table class="table flat">
                    <thead>
                        <tr>
                            <th>Urut</th>
                            <th>Gambar</th>
                            <th>Judul</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($rows ?? []) as $r): ?>
                            <tr>
                                <td style="white-space:nowrap;">
                                    <form action="<?= site_url('multiuser/hero/move-up/' . $r['id']) ?>" method="post"
                                        style="display:inline"><?= csrf_field() ?><button class="btn small">▲</button>
                                    </form>
                                    <form action="<?= site_url('multiuser/hero/move-down/' . $r['id']) ?>" method="post"
                                        style="display:inline"><?= csrf_field() ?><button class="btn small">▼</button>
                                    </form>
                                    <span class="muted" style="margin-left:6px;"><?= (int) $r['sort_order'] ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($r['image'])): ?>
                                        <img src="<?= base_url('images/hero/' . $r['image']) ?>" alt=""
                                            style="height:48px;border-radius:6px;">
                                    <?php else: ?> — <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-bold"><?= esc($r['title'] ?? '-') ?></div>
                                    <div class="muted"><?= esc($r['tagline'] ?? '') ?></div>
                                </td>
                                <td><?= $r['is_active'] ? '<span class="badge success">Aktif</span>' : '<span class="badge">Nonaktif</span>' ?>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a class="btn small warn"
                                        href="<?= site_url('multiuser/hero/edit/' . $r['id']) ?>">Edit</a>
                                    <form action="<?= site_url('multiuser/hero/toggle/' . $r['id']) ?>" method="post"
                                        style="display:inline"><?= csrf_field() ?><button
                                            class="btn small"><?= $r['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                    </form>
                                    <form action="<?= site_url('multiuser/hero/delete/' . $r['id']) ?>" method="post"
                                        style="display:inline" onsubmit="return confirm('Hapus banner ini?')">
                                        <?= csrf_field() ?><button class="btn small danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<?= $this->endSection() ?>