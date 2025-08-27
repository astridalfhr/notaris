<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>
    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-rectangle-list"></i> Berita</h2>
            <a class="btn success" href="<?= site_url('multiuser/news/create') ?>"
                style="background:#16A34A;color:#fff;border:none;padding:10px 14px;border-radius:10px;text-decoration:none;">+
                Tambah Berita</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="card" style="border-left:4px solid #10B981;"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php elseif (session()->getFlashdata('error')): ?>
            <div class="card" style="border-left:4px solid #EF4444;"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <div class="card">
            <h3 class="card-title"><i class="fa-solid fa-list"></i> Daftar Berita</h3>
            <table class="table flat">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Publish</th>
                        <th>Featured</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    foreach (($rows ?? []) as $r): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= esc($r['title']) ?></td>
                            <td><?= $r['is_published'] ? esc(date('d M Y H:i', strtotime($r['published_at']))) : '<span class="muted">Draft</span>' ?>
                            </td>
                            <td><?= $r['is_featured'] ? 'Ya' : '—' ?></td>
                            <td>
                                <a class="btn small warn" href="<?= site_url('multiuser/news/edit/' . $r['id']) ?>">Edit</a>
                                <form action="<?= site_url('multiuser/news/delete/' . $r['id']) ?>" method="post"
                                    style="display:inline" onsubmit="return confirm('Hapus berita ini?')">
                                    <?= csrf_field() ?><button class="btn small danger">Hapus</button>
                                </form>
                                <form action="<?= site_url('multiuser/news/feature/' . $r['id']) ?>" method="post"
                                    style="display:inline">
                                    <?= csrf_field() ?><button
                                        class="btn small"><?= $r['is_featured'] ? 'Lepas Featured' : 'Jadikan Featured' ?></button>
                                </form>
                                <form action="<?= site_url('multiuser/news/publish/' . $r['id']) ?>" method="post"
                                    style="display:inline">
                                    <?= csrf_field() ?><button
                                        class="btn small"><?= $r['is_published'] ? 'Unpublish' : 'Publish' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?= $this->endSection() ?>