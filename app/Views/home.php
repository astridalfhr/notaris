<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$settings = $settings ?? [];
$servicesPPAT = $servicesPPAT ?? [];
$servicesNotaris = $servicesNotaris ?? [];

// fallback foto owner
if (!function_exists('ownerImageUrl')) {
  function ownerImageUrl(?string $fn): string
  {
    return $fn ? base_url('images/owner/' . $fn) : base_url('images/pemilik.jpg');
  }
}
?>

<style>
  .line-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden
  }

  .line-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden
  }
</style>

<!-- ===== Header ringkas ===== -->
<section class="bg-white py-12">
  <div class="mx-auto max-w-5xl px-4">
    <h1 class="text-2xl md:text-3xl font-extrabold text-center text-stone-800">
      Layanan Notaris & PPAT
    </h1><br>
    <p class="text-center text-stone-600 mt-2">
      Kantor Notaris dan PPAT memberikan layanan penyusunan serta pengesahan akta otentik, baik terkait perjanjian
      hukum, pendirian perusahaan, maupun urusan pertanahan seperti jual beli, hibah, dan pembagian hak. Melalui layanan
      ini, masyarakat memperoleh kepastian hukum, perlindungan hak, serta legalitas yang sah sesuai ketentuan peraturan
      perundang-undangan.
    </p>
  </div>
</section>

<!-- ===== Section 1: PPAT (kartu besar + jarak rapi) ===== -->
<section class="mx-auto max-w-5xl px-4 py-10">
  <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
    <div class="flex items-center justify-between mb-5">
      <h2 class="text-xl font-bold text-stone-900">Pekerjaan PPAT</h2>
      <div class="flex items-center gap-2">
        <select class="svc-select px-3 py-2 border border-stone-300 rounded-lg text-sm" data-target="#ppat-list">
          <option value="3" selected>3 teratas</option>
          <option value="all">Semua</option>
        </select>
        <button class="svc-toggle px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm"
          data-target="#ppat-list" data-state="3">Lihat semua</button>
      </div>
    </div>

    <?php if (empty($servicesPPAT)): ?>
      <div class="rounded-xl border border-dashed p-6 text-stone-600 bg-white">Belum ada data.</div>
    <?php else: ?>
      <!-- grid kartu persegi -->
      <div id="ppat-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($servicesPPAT as $idx => $s): ?>
          <article
            class="svc-item <?= $idx >= 3 ? 'hidden' : '' ?> bg-white border border-stone-200 rounded-2xl p-4 shadow-sm hover:shadow-md transition aspect-square flex flex-col">
            <div class="flex items-center gap-2 text-amber-700">
              <div class="w-9 h-9 rounded-xl bg-amber-100 grid place-items-center">
                <i class="fa-solid <?= esc($s['icon'] ?? 'fa-file-lines') ?>"></i>
              </div>
              <span class="text-[11px] uppercase tracking-wide text-stone-500">PPAT</span>
            </div>
            <h3 class="mt-2 font-semibold text-stone-900 text-sm md:text-base line-2"><?= esc($s['name']) ?></h3>
            <p class="mt-1 text-[12px] text-stone-600 line-3">
              <?= esc($s['desc'] ?? 'Klik untuk melihat ketersediaan jadwal.') ?>
            </p>
            <div class="mt-auto pt-3">
              <a href="<?= site_url('layanan?service=' . rawurlencode($s['slug'])) ?>"
                class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-xs">
                Lihat Jadwal
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== Section 2: Notaris (kartu besar terpisah + jarak) ===== -->
<section class="mx-auto max-w-5xl px-4 py-10">
  <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8">
    <div class="flex items-center justify-between mb-5">
      <h2 class="text-xl font-bold text-stone-900">Pekerjaan Notaris</h2>
      <div class="flex items-center gap-2">
        <select class="svc-select px-3 py-2 border border-stone-300 rounded-lg text-sm" data-target="#notaris-list">
          <option value="3" selected>3 teratas</option>
          <option value="all">Semua</option>
        </select>
        <button class="svc-toggle px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-sm"
          data-target="#notaris-list" data-state="3">Lihat semua</button>
      </div>
    </div>

    <?php if (empty($servicesNotaris)): ?>
      <div class="rounded-xl border border-dashed p-6 text-stone-600 bg-white">Belum ada data.</div>
    <?php else: ?>
      <div id="notaris-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($servicesNotaris as $idx => $s): ?>
          <article
            class="svc-item <?= $idx >= 3 ? 'hidden' : '' ?> bg-white border border-stone-200 rounded-2xl p-4 shadow-sm hover:shadow-md transition aspect-square flex flex-col">
            <div class="flex items-center gap-2 text-amber-700">
              <div class="w-9 h-9 rounded-xl bg-amber-100 grid place-items-center">
                <i class="fa-solid <?= esc($s['icon'] ?? 'fa-file-lines') ?>"></i>
              </div>
              <span class="text-[11px] uppercase tracking-wide text-stone-500">Notaris</span>
            </div>
            <h3 class="mt-2 font-semibold text-stone-900 text-sm md:text-base line-2"><?= esc($s['name']) ?></h3>
            <p class="mt-1 text-[12px] text-stone-600 line-3">
              <?= esc($s['desc'] ?? 'Klik untuk melihat ketersediaan jadwal.') ?>
            </p>
            <div class="mt-auto pt-3">
              <a href="<?= site_url('layanan?service=' . rawurlencode($s['slug'])) ?>"
                class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg bg-amber-600 text-white hover:bg-amber-700 text-xs">
                Lihat Jadwal
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== Bagian bawah (owner/about/kontak/map) — tetap, dengan jarak ekstra ===== -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<section class="py-16 bg-gray-100">
  <div class="mx-auto max-w-5xl px-4 grid md:grid-cols-2 gap-8">
    <div class="bg-white rounded-2xl shadow-xl p-8 flex items-center space-x-6" data-aos="fade-up" data-aos-delay="100">
      <img src="<?= esc(ownerImageUrl($settings['owner_photo'] ?? null)) ?>" alt="Pemilik"
        class="w-28 h-28 md:w-36 md:h-36 rounded-full object-cover shadow-md">
      <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-2"><?= esc($settings['owner_name'] ?? 'Nama Pemilik') ?></h2>
        <p class="text-gray-600 leading-relaxed text-sm md:text-base">
          <?= esc($settings['owner_subtitle'] ?? 'Profil singkat.') ?>
        </p>
        <a href="<?= site_url('profile') ?>"
          class="inline-flex items-center mt-4 text-white bg-amber-600 hover:bg-amber-700 px-4 py-2 rounded-xl text-sm font-medium shadow transition duration-300">
          Lihat Profil Lengkap
        </a>
      </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8 flex flex-col md:flex-row items-center gap-6" data-aos="fade-up"
      data-aos-delay="200">
      <img src="<?= base_url('images/kantor.jpg') ?>" alt="Kantor"
        class="w-full md:w-1/3 h-36 object-cover rounded-xl shadow-md" onerror="this.style.display='none'" />
      <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-2"><?= esc($settings['about_title'] ?? 'Visi & Misi') ?></h2>
        <div class="text-gray-700 prose max-w-none">
          <?= !empty($settings['about_body']) ? $settings['about_body'] : '<p>Tambahkan deskripsi di Kelola Beranda.</p>' ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-white py-12">
  <div class="mx-auto max-w-5xl text-center px-4">
    <h2 class="text-2xl font-semibold mb-6 text-gray-800">Hubungi Kami</h2>
    <div class="flex justify-center gap-6 text-gray-600 text-2xl">
      <?php if (!empty($settings['social_email'])): ?>
        <a href="mailto:<?= esc($settings['social_email']) ?>" class="hover:text-blue-600 transition" title="Email"><i
            class="fa-solid fa-envelope"></i></a>
      <?php endif; ?>
      <?php if (!empty($settings['social_instagram'])): ?>
        <a href="<?= esc($settings['social_instagram']) ?>" target="_blank" class="hover:text-pink-500 transition"
          title="Instagram"><i class="fa-brands fa-instagram"></i></a>
      <?php endif; ?>
      <?php if (!empty($settings['social_whatsapp'])): ?>
        <a href="https://wa.me/<?= esc(preg_replace('/\D/', '', $settings['social_whatsapp'])) ?>" target="_blank"
          class="hover:text-green-500 transition" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
      <?php endif; ?>
      <?php if (!empty($settings['social_linkedin'])): ?>
        <a href="<?= esc($settings['social_linkedin']) ?>" target="_blank" class="hover:text-blue-700 transition"
          title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="bg-gray-100 py-12">
  <div class="mx-auto max-w-5xl px-4 text-center">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Lokasi Kantor</h2>
    <div class="rounded-xl overflow-hidden shadow-lg">
      <?php if (!empty($settings['map_embed'])): ?>
        <?= $settings['map_embed'] ?>
      <?php else: ?>
        <iframe
          src="https://www.google.com/maps?q=<?= urlencode($settings['address'] ?? 'Kantor Notaris') ?>&output=embed"
          width="100%" height="420" style="border:0;" allowfullscreen loading="lazy"
          referrerpolicy="no-referrer-when-downgrade" class="w-full rounded-xl"></iframe>
      <?php endif; ?>
    </div>
  </div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script> AOS.init({ duration: 800, once: true }); </script>

<!-- Toggle & Dropdown -->
<script>
  function showTop3(container) { container.querySelectorAll('.svc-item').forEach((el, i) => i < 3 ? el.classList.remove('hidden') : el.classList.add('hidden')); }
  function showAll(container) { container.querySelectorAll('.svc-item').forEach(el => el.classList.remove('hidden')); }

  document.querySelectorAll('.svc-select').forEach(sel => {
    const target = document.querySelector(sel.dataset.target);
    sel.addEventListener('change', () => {
      if (!target) return;
      if (sel.value === 'all') showAll(target); else showTop3(target);
      const btn = document.querySelector(`.svc-toggle[data-target="${sel.dataset.target}"]`);
      if (btn) { if (sel.value === 'all') { btn.dataset.state = 'all'; btn.textContent = 'Tampilkan 3'; } else { btn.dataset.state = '3'; btn.textContent = 'Lihat semua'; } }
    });
  });

  document.querySelectorAll('.svc-toggle').forEach(btn => {
    const target = document.querySelector(btn.dataset.target);
    btn.addEventListener('click', () => {
      if (!target) return;
      if (btn.dataset.state === 'all') {
        showTop3(target); btn.dataset.state = '3'; btn.textContent = 'Lihat semua';
        const sel = document.querySelector(`.svc-select[data-target="${btn.dataset.target}"]`); if (sel) sel.value = '3';
      } else {
        showAll(target); btn.dataset.state = 'all'; btn.textContent = 'Tampilkan 3';
        const sel = document.querySelector(`.svc-select[data-target="${btn.dataset.target}"]`); if (sel) sel.value = 'all';
      }
    });
  });
</script>

<!-- Font Awesome (pastikan di layout utama atau aktifkan baris ini sekali saja) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<?= $this->endSection() ?>