<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="admin-layout">
    <?= $this->include('layouts/multiuser_sidebar') ?>

    <main class="admin-main">
        <div class="admin-head">
            <h2><i class="fa-solid fa-briefcase"></i> <?= esc($title) ?></h2>
            <a class="btn-grad" href="<?= site_url('multiuser/kerja') ?>"><i class="fa-solid fa-list"></i> Rekap</a>
        </div>

        <p class="muted" style="margin-bottom:16px;"><?= esc($desc) ?></p>

        <form action="<?= site_url('multiuser/kerja/upload') ?>" method="post" enctype="multipart/form-data" class="card">
            <?= csrf_field() ?>
            <input type="hidden" name="origin" value="<?= esc($kategori . '/' . $slug) ?>">
            <div class="form-row">
                <label>Upload Berkas</label>
                <input type="file" name="berkas" required>
            </div>
            <button type="submit" class="btn-grad"><i class="fa-solid fa-upload"></i> Unggah</button>
        </form>

        <h3 class="block-title"><i class="fa-solid fa-folder-open"></i> Berkas Saya</h3>
        <table class="table">
            <thead>
                <tr>
                    <th style="width:56px">No</th>
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
                            <td class="file-name"><?= esc($f['name']) ?></td>
                            <td><?= $f['mtime'] ? date('d M Y H:i', (int) $f['mtime']) : '-' ?></td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="btn small warn js-preview" data-url="<?= esc($f['url']) ?>"
                                        data-name="<?= esc($f['name']) ?>">
                                        <i class="fa-solid fa-eye"></i> Lihat
                                    </button>

                                    <a class="btn small" href="<?= esc($f['url']) ?>" download>
                                        <i class="fa-solid fa-download"></i> Unduh
                                    </a>

                                    <form action="<?= site_url('multiuser/kerja/delete') ?>" method="post" style="display:inline"
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
                        <td colspan="4" class="empty">Belum ada berkas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Modal preview (sama dengan kerja_index) -->
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

<!-- Script preview (sama dengan kerja_index) -->
<script>
    // copy-paste script dari kerja_index biar konsisten
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<?= $this->endSection() ?>