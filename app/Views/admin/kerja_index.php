<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <?= $this->include('layouts/admin_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-briefcase"></i> Halaman Kerja</h2>
        </div>

        <div class="card">
            <h3 class="card-title"><i class="fa-solid fa-sitemap"></i> Navigasi</h3>
            <div class="stat-grid">
                <?php foreach ($menu as $kat => $items): ?>
                    <div class="stat-card">
                        <div class="stat-num"><?= strtoupper($kat) ?></div>
                        <div class="stat-lbl">
                            <?php foreach ($items as $it): ?>
                                <div>
                                    <a href="<?= site_url('admin/kerja/' . $kat . '/' . $it['slug']) ?>">
                                        <?= esc($it['title']) ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <h3 class="block-title"><i class="fa-solid fa-folder-open"></i> Rekap Berkas Terbaru</h3>
        <table class="table">
            <thead>
                <tr>
                    <th style="width:56px">No</th>
                    <th>Kategori</th>
                    <th>Sub</th>
                    <th>Nama File</th>
                    <th>Diunggah</th>
                    <th style="width:260px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($files)):
                    $i = 1;
                    foreach ($files as $f): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= $f['kategori'] !== '-' ? strtoupper($f['kategori']) : '—' ?></td>
                            <td><?= $f['subkategori'] !== '-' ? strtoupper($f['subkategori']) : '—' ?></td>
                            <td class="file-name"><?= esc($f['name']) ?></td>
                            <td><?= $f['mtime'] ? date('d M Y H:i', (int) $f['mtime']) : '-' ?></td>
                            <td>
                                <div class="row-actions">
                                    <!-- tombol lihat pakai js-preview -->
                                    <button type="button" class="btn small warn js-preview" data-url="<?= esc($f['url']) ?>"
                                        data-name="<?= esc($f['name']) ?>">
                                        <i class="fa-solid fa-eye"></i> Lihat
                                    </button>

                                    <?php if ($f['kategori'] !== '-' && $f['subkategori'] !== '-'): ?>
                                        <a class="btn small"
                                            href="<?= site_url('admin/kerja/' . $f['kategori'] . '/' . $f['subkategori']) ?>">
                                            <i class="fa-solid fa-folder-open"></i> Buka Folder
                                        </a>
                                    <?php else: ?>
                                        <span class="btn small muted">Tidak Ada Folder</span>
                                    <?php endif; ?>

                                    <a class="btn small" href="<?= esc($f['url']) ?>" download>
                                        <i class="fa-solid fa-download"></i> Unduh
                                    </a>

                                    <form action="<?= site_url('admin/kerja/delete') ?>" method="post" style="display:inline"
                                        onsubmit="return confirm('Hapus file ini?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="rel" value="<?= esc($f['rel']) ?>">
                                        <button type="submit" class="btn small danger">
                                            <i class="fa-solid fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="empty">Belum ada berkas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Modal preview -->
        <div id="filePreviewModal" class="preview-modal" aria-hidden="true">
            <div class="pm-backdrop"></div>
            <div class="pm-dialog">
                <div class="pm-header">
                    <h4 class="pm-title">Pratinjau Berkas</h4>
                    <button class="pm-close" aria-label="Tutup">×</button>
                </div>
                <div class="pm-body"></div>
                <div class="pm-footer">
                    <a class="btn warn pm-open" target="_blank" rel="noopener">
                        <i class="fa-solid fa-up-right-from-square"></i> Lihat di Tab Baru
                    </a>
                    <a class="btn pm-download" download>
                        <i class="fa-solid fa-download"></i> Unduh
                    </a>
                    <button class="btn danger pm-close">
                        <i class="fa-solid fa-xmark"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Script preview -->
<script>
    (function () {
        const modal = document.getElementById('filePreviewModal');
        const bodyEl = modal.querySelector('.pm-body');
        const titleEl = modal.querySelector('.pm-title');
        const openEl = modal.querySelector('.pm-open');
        const dlEl = modal.querySelector('.pm-download');
        const closes = modal.querySelectorAll('.pm-close');

        function ext(url) {
            const clean = (url || '').split('?')[0].split('#')[0];
            const m = clean.match(/\.([^.\/\\]+)$/i);
            return m ? m[1].toLowerCase() : '';
        }
        function officeEmbed(url) { return 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodeURIComponent(url); }

        function show(url, name) {
            titleEl.textContent = name || 'Pratinjau Berkas';
            bodyEl.innerHTML = '';

            const e = ext(url);
            if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'].includes(e)) {
                const img = document.createElement('img');
                img.src = url; img.alt = name || '';
                img.className = 'pm-img';
                bodyEl.innerHTML = '<div class="pm-img-wrap"></div>';
                bodyEl.firstChild.appendChild(img);
            } else if (['pdf'].includes(e)) {
                const iframe = document.createElement('iframe');
                iframe.src = url + '#zoom=page-fit';
                iframe.className = 'pm-frame';
                iframe.setAttribute('loading', 'lazy');
                bodyEl.appendChild(iframe);
            } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(e)) {
                const iframe = document.createElement('iframe');
                iframe.src = officeEmbed(url);
                iframe.className = 'pm-frame';
                iframe.setAttribute('loading', 'lazy');
                bodyEl.appendChild(iframe);
            } else {
                bodyEl.innerHTML = '<p class="muted">Preview tidak tersedia. Gunakan tombol di bawah untuk membuka atau mengunduh.</p>';
            }

            openEl.href = url;
            dlEl.href = url;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function hide() {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
            bodyEl.innerHTML = '';
        }

        document.addEventListener('click', function (ev) {
            const t = ev.target.closest('.js-preview');
            if (t) {
                ev.preventDefault();
                show(t.dataset.url, t.dataset.name);
            }
            if (ev.target.classList.contains('pm-backdrop')) hide();
        });
        closes.forEach(btn => btn.addEventListener('click', hide));
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('is-open')) hide(); });
    })();
</script>

<link rel="stylesheet" href="<?= base_url('assets/css/admin-kerja.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<?= $this->endSection() ?>