<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
function serviceImageUrl(?string $fn): string
{
    return $fn ? base_url('images/services/' . $fn) : 'https://via.placeholder.com/640x360?text=Layanan';
}
?>

<section class="container my-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="fw-bold m-0">Layanan Kami</h3>
        <a href="<?= site_url('layanan') ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>

    <?php if (!empty($services)): ?>
        <div class="row g-3">
            <?php foreach ($services as $s):
                $title = $s['name'] ?? ($s['title'] ?? 'Layanan');
                $slug = $s['slug'] ?? (string) ($s['id'] ?? '');
                $desc = $s['short_desc'] ?? ($s['long_desc'] ?? ($s['body'] ?? ''));
                // Coba pecah 'requirements' jadi bullet, kalau ada:
                $reqs = [];
                if (!empty($s['requirements'])) {
                    $reqs = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', strip_tags($s['requirements']))));
                }
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-card h-100 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="service-badge"><?= esc($s['category'] ?? 'Layanan') ?></span>
                            <!-- optional ikon kecil -->
                        </div>

                        <div class="ratio ratio-16x9 mb-3 rounded" style="overflow:hidden;">
                            <img src="<?= esc(serviceImageUrl($s['image'] ?? null)) ?>" alt="<?= esc($title) ?>"
                                class="w-100 h-100 object-fit-cover">
                        </div>

                        <h5 class="mb-2"><?= esc($title) ?></h5>
                        <p class="text-muted mb-3"><?= esc(mb_strimwidth(strip_tags($desc), 0, 160, '…')) ?></p>

                        <?php if (!empty($reqs)): ?>
                            <ul class="service-feats list-unstyled mb-3">
                                <?php foreach (array_slice($reqs, 0, 4) as $r): ?>
                                    <li>
                                        <!-- mini check icon -->
                                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <span><?= esc($r) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="service-cta">
                            <!-- Tombol ke jadwal karyawan yang menguasai layanan ini (lihat bagian Routes + Controller) -->
                            <a href="<?= site_url('jadwal?service=' . $slug) ?>" class="btn btn-primary btn-sm">Lihat Jadwal</a>
                            <a href="<?= site_url('layanan/' . $slug) ?>" class="btn btn-outline-secondary btn-sm">Detail
                                Layanan</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Belum ada layanan ditampilkan.</div>
    <?php endif; ?>
</section>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<section class="py-16 bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 md:px-8 grid md:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 flex items-center space-x-6" data-aos="fade-up"
            data-aos-delay="100">
            <img src="<?= esc(ownerImageUrl($settings['owner_photo'] ?? null)) ?>" alt="Pemilik Notaris"
                class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover shadow-md">
            <div>
                <h2 class="text-3xl font-semibold text-gray-800 mb-2">
                    <?= esc($settings['owner_name'] ?? 'Nama Pemilik') ?></h2>
                <p class="text-gray-600 leading-relaxed text-sm md:text-base">
                    <?= esc($settings['owner_subtitle'] ?? 'Profil singkat pemilik atau jabatan.') ?>
                </p>
                <a href="<?= site_url('profile') ?>"
                    class="inline-flex items-center mt-4 text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-xl text-sm font-medium shadow transition duration-300">
                    Lihat Profil Lengkap
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col md:flex-row items-center gap-6" data-aos="fade-up"
            data-aos-delay="200">
            <img src="<?= base_url('images/kantor.jpg') ?>" alt="Kantor Notaris"
                class="w-full md:w-1/3 h-40 object-cover rounded-xl shadow-md" onerror="this.style.display='none'" />
            <div>
                <h2 class="text-3xl font-semibold text-gray-800 mb-2">
                    <?= esc($settings['about_title'] ?? 'Visi & Misi') ?></h2>
                <div class="text-gray-700 prose max-w-none">
                    <?= !empty($settings['about_body']) ? $settings['about_body'] : '<p>Tambahkan deskripsi perusahaan, visi & misi di halaman Kelola Beranda.</p>' ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="bg-white py-12">
    <div class="max-w-6xl mx-auto text-center">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Hubungi Kami</h2>
        <div class="flex justify-center gap-6 text-gray-600 text-2xl">
            <?php if (!empty($settings['social_email'])): ?>
                <a href="mailto:<?= esc($settings['social_email']) ?>" class="hover:text-blue-600 transition"
                    title="Email"><i class="fas fa-envelope"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['social_instagram'])): ?>
                <a href="<?= esc($settings['social_instagram']) ?>" target="_blank" class="hover:text-pink-500 transition"
                    title="Instagram"><i class="fab fa-instagram"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['social_whatsapp'])): ?>
                <a href="https://wa.me/<?= esc(preg_replace('/\D/', '', $settings['social_whatsapp'])) ?>" target="_blank"
                    class="hover:text-green-500 transition" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['social_linkedin'])): ?>
                <a href="<?= esc($settings['social_linkedin']) ?>" target="_blank" class="hover:text-blue-700 transition"
                    title="LinkedIn"><i class="fab fa-linkedin"></i></a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="bg-gray-100 py-12">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Lokasi Kantor Notaris</h2>
        <div class="rounded-xl overflow-hidden shadow-lg">
            <?php if (!empty($settings['map_embed'])): ?>
                <?= $settings['map_embed'] ?>
            <?php else: ?>
                <iframe
                    src="https://www.google.com/maps?q=<?= urlencode($settings['address'] ?? 'Kantor Notaris') ?>&output=embed"
                    width="100%" height="450" style="border:0;" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" class="w-full rounded-xl"></iframe>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>