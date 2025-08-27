<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$company = $company ?? [];
$employees = $employees ?? [];

// pastikan semua key ada supaya tidak error undefined
$company = array_merge([
    'name' => null,
    'summary' => null,
    'owner_name' => null,
    'owner_subtitle' => null,
    'owner_photo' => null,
    'address' => null,
    'map_embed' => null,
    'social_email' => null,
    'social_instagram' => null,
    'social_whatsapp' => null,
    'social_linkedin' => null,
], $company);

function safe($v)
{
    return esc((string) ($v ?? ''));
}
$ownerImg = !empty($company['owner_photo'])
    ? $company['owner_photo']
    : base_url('images/pemilik.jpg');
?>

<!-- ===== Profil Perusahaan (optimized) ===== -->
<section class="container mx-auto px-6 py-10">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="grid md:grid-cols-12 gap-0">

            <!-- Owner / Branding -->
            <div
                class="md:col-span-4 p-8 bg-gradient-to-b from-gray-50 to-white flex flex-col items-center text-center">
                <div class="w-32 h-32 rounded-full overflow-hidden shadow-md mb-4">
                    <img src="<?= esc($ownerImg) ?>" alt="Owner" class="w-full h-full object-cover"
                        onerror="this.src='https://via.placeholder.com/200?text=Owner'">
                </div>
                <h2 class="text-xl font-semibold text-gray-800">
                    <?= safe(($company['owner_name'] ?? '') ?: 'Pemilik Perusahaan') ?>
                </h2>
                <p class="text-sm text-gray-500">
                    <?= safe(($company['owner_subtitle'] ?? '') ?: '—') ?>
                </p>

                <div class="mt-6 w-full">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Kontak & Sosial</h3>
                    <div class="flex items-center justify-center gap-4 text-gray-600 text-xl">
                        <?php if (!empty($company['social_email'])): ?>
                            <a href="mailto:<?= esc($company['social_email']) ?>" class="hover:text-blue-600"
                                title="Email"><i class="fas fa-envelope"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($company['social_instagram'])): ?>
                            <a href="<?= esc($company['social_instagram']) ?>" target="_blank" class="hover:text-pink-500"
                                title="Instagram"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($company['social_whatsapp'])): ?>
                            <a href="https://wa.me/<?= esc(preg_replace('/\D/', '', $company['social_whatsapp'])) ?>"
                                target="_blank" class="hover:text-green-500" title="WhatsApp"><i
                                    class="fab fa-whatsapp"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($company['social_linkedin'])): ?>
                            <a href="<?= esc($company['social_linkedin']) ?>" target="_blank" class="hover:text-blue-700"
                                title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Company About -->
            <div class="md:col-span-8 p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            <?= safe(($company['name'] ?? '') ?: 'Profil Perusahaan') ?>
                        </h1>
                        <?php if (!empty($company['address'])): ?>
                            <p class="text-sm text-gray-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i>
                                <?= safe($company['address']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if (strtolower((string) session('role')) === 'multiuser'): ?>
                        <a href="<?= site_url('multiuser/company') ?>"
                            class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Profil
                        </a>
                    <?php endif; ?>
                </div>

                <div class="prose max-w-none text-gray-700 mt-5">
                    <?php if (!empty($company['summary'])): ?>
                        <?= $company['summary'] ?>
                    <?php else: ?>
                        <p>Tambahkan deskripsi perusahaan melalui menu <em>Profil Perusahaan</em> di panel Multiuser.</p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($company['map_embed'])): ?>
                    <div class="mt-6">
                        <div class="rounded-xl overflow-hidden shadow">
                            <?= $company['map_embed'] ?>
                        </div>
                    </div>
                <?php elseif (!empty($company['address'])): ?>
                    <div class="mt-6">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($company['address']) ?>"
                            target="_blank"
                            class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700">
                            <i class="fa-solid fa-map-location-dot mr-2"></i> Lihat Lokasi
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<!-- ===== Daftar Karyawan ===== -->
<section class="py-12 bg-gray-100">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Profil Karyawan</h2>

    <div id="employee-list"
        class="grid md:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-6 px-6 max-w-7xl mx-auto transition-all duration-300">

        <?php if (!empty($employees)): ?>
            <?php foreach ($employees as $index => $emp):
                $foto = (string) ($emp['foto_url'] ?? '');
                if ($foto === '')
                    $foto = 'https://via.placeholder.com/150';
                ?>
                <button type="button"
                    class="employee-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-300 text-left"
                    data-index="<?= (int) $index ?>">
                    <img src="<?= esc($foto) ?>" alt="<?= esc($emp['nama'] ?? 'Karyawan') ?>"
                        class="w-24 h-24 rounded-full mx-auto mb-4 object-cover" />
                    <h3 class="text-lg font-semibold text-center"><?= safe($emp['nama'] ?? '-') ?></h3>
                    <p class="text-sm text-gray-600 text-center"><?= safe($emp['jabatan'] ?? '-') ?></p>
                    <p class="text-xs text-gray-400 text-center"><?= safe($emp['email'] ?? '-') ?></p>
                </button>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center text-gray-500">Belum ada data karyawan aktif.</div>
        <?php endif; ?>

    </div>

    <?php if (strtolower((string) session('role')) === 'multiuser'): ?>
        <div class="mt-8 text-center">
            <a href="<?= site_url('multiuser/employees') ?>"
                class="px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
                <i class="fa-solid fa-users-gear mr-1"></i> Kelola Karyawan
            </a>
        </div>
    <?php endif; ?>
</section>

<!-- ===== Modal Detail Karyawan ===== -->
<div id="employee-modal" class="fixed inset-0 z-50 hidden">
    <div id="employee-backdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative max-w-3xl mx-auto mt-10 bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 id="modal-title" class="text-xl font-semibold text-gray-800">Detail Karyawan</h3>
            <button id="modal-close"
                class="rounded-lg px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700">Tutup</button>
        </div>
        <div class="p-6">
            <div class="md:flex items-center gap-8">
                <img id="modal-foto" src="https://via.placeholder.com/150"
                    class="w-40 h-40 rounded-full object-cover mx-auto md:mx-0 mb-4 md:mb-0" alt="Foto Karyawan">
                <div class="min-w-0">
                    <h4 id="modal-nama" class="text-2xl font-bold mb-2">-</h4>
                    <p class="text-gray-600 mb-1"><span class="font-medium">Posisi:</span> <span
                            id="modal-jabatan">-</span></p>
                    <p class="text-gray-600 mb-1"><span class="font-medium">Email:</span> <span
                            id="modal-email">-</span></p>
                    <p class="text-gray-600 mb-1"><span class="font-medium">Spesialisasi:</span> <span
                            id="modal-spesialisasi">-</span></p>
                </div>
            </div>
            <div class="mt-6">
                <h5 class="text-gray-800 font-semibold mb-2">Deskripsi</h5>
                <p id="modal-deskripsi" class="text-gray-700 whitespace-pre-line">-</p>
            </div>
        </div>
    </div>
</div>

<script>
    const employees = <?= json_encode($employees ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

    const modal = document.getElementById('employee-modal');
    const backdrop = document.getElementById('employee-backdrop');
    const closeBtn = document.getElementById('modal-close');
    const fotoEl = document.getElementById('modal-foto');
    const titleEl = document.getElementById('modal-title');
    const namaEl = document.getElementById('modal-nama');
    const jabatanEl = document.getElementById('modal-jabatan');
    const emailEl = document.getElementById('modal-email');
    const spesialisasiEl = document.getElementById('modal-spesialisasi');
    const deskripsiEl = document.getElementById('modal-deskripsi');
    const text = (v) => (v && typeof v === 'string') ? v : (v ?? '').toString();

    function openProfile(index) {
        const data = employees[index];
        if (!data) return;

        const foto = data.foto_url && data.foto_url.length ? data.foto_url : 'https://via.placeholder.com/150';
        fotoEl.src = foto;
        fotoEl.alt = (data.nama ?? 'Karyawan');

        titleEl.textContent = 'Detail Karyawan';
        namaEl.textContent = text(data.nama ?? '-');
        jabatanEl.textContent = text(data.jabatan ?? '-');
        emailEl.textContent = text(data.email ?? '-');
        spesialisasiEl.textContent = text(data.spesialisasi ?? '-');

        const desc = (data.deskripsi && data.deskripsi.trim() !== '')
            ? data.deskripsi
            : `Deskripsi singkat tentang ${(data.nama ?? 'karyawan')} belum ditambahkan.`;
        deskripsiEl.textContent = desc;

        modal.classList.remove('hidden');
        document.documentElement.style.overflow = 'hidden';
    }

    function closeProfile() {
        modal.classList.add('hidden');
        document.documentElement.style.overflow = '';
    }

    document.getElementById('employee-list')?.addEventListener('click', (e) => {
        const btn = e.target.closest('.employee-card');
        if (!btn) return;
        const idx = btn.getAttribute('data-index');
        openProfile(parseInt(idx, 10));
    });

    backdrop.addEventListener('click', closeProfile);
    closeBtn.addEventListener('click', closeProfile);
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeProfile(); });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>