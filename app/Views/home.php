<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$settings = $settings ?? [];
$heroes = $heroes ?? [];
$featured = $featured ?? [];
$latest = $latest ?? [];

function newsImageUrl(?string $fn): string
{
  if (!$fn)
    return 'https://via.placeholder.com/600x400?text=News';
  if (filter_var($fn, FILTER_VALIDATE_URL))
    return $fn;
  return base_url('images/news/' . $fn);
}
function heroImageUrl(?string $fn): string
{
  return $fn ? base_url('images/hero/' . $fn) : base_url('images/kantor.jpg');
}
function ownerImageUrl(?string $fn): string
{
  return $fn ? base_url('images/owner/' . $fn) : base_url('images/pemilik.jpg');
}
function fmtDate(?string $ts): string
{
  if (!$ts)
    return '-';
  $t = strtotime($ts);
  return $t ? date('d M Y', $t) : '-';
}

$useHeroes = !empty($heroes);
$slidesData = $useHeroes ? $heroes : $featured;
$dotCount = max(1, count($slidesData));
?>

<section class="relative bg-gray-100 h-[600px] flex items-center justify-center">
  <div id="slider" class="relative max-w-5xl w-full shadow-lg rounded-lg overflow-hidden bg-white">
    <div class="relative overflow-hidden">
      <div class="slides flex transition-transform duration-700 ease-in-out" style="transform: translateX(0%)"
        id="slidesContainer">
        <?php if (!empty($slidesData)): ?>
          <?php foreach ($slidesData as $it): ?>
            <article class="min-w-full p-10 flex flex-col md:flex-row items-center gap-10">
              <?php if ($useHeroes): ?>
                <img src="<?= esc(heroImageUrl($it['image'] ?? null)) ?>" alt="<?= esc($it['title'] ?? 'Banner') ?>"
                  class="w-72 h-72 object-cover rounded-md shadow-md" onerror="this.style.display='none'" />
                <div class="max-w-xl">
                  <p class="uppercase tracking-widest text-gray-500 mb-2">Banner</p>
                  <h2 class="text-4xl font-extrabold mb-3"><?= esc($it['title'] ?? '-') ?></h2>
                  <?php if (!empty($it['tagline'])): ?>
                    <p class="text-gray-700 leading-relaxed mb-4"><?= esc($it['tagline']) ?></p>
                  <?php elseif (!empty($settings['hero_tagline'])): ?>
                    <p class="text-gray-700 leading-relaxed mb-4"><?= esc($settings['hero_tagline']) ?></p>
                  <?php endif; ?>
                  <?php if (!empty($it['button_text']) && !empty($it['button_link'])): ?>
                    <a href="<?= esc($it['button_link']) ?>"
                      class="inline-block px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"><?= esc($it['button_text']) ?></a>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <img src="<?= esc(newsImageUrl($it['image'] ?? null)) ?>" alt="<?= esc($it['title'] ?? 'Featured') ?>"
                  class="w-72 h-72 object-cover rounded-md shadow-md" onerror="this.style.display='none'" />
                <div class="max-w-xl">
                  <p class="uppercase tracking-widest text-gray-500 mb-2">Hot News</p>
                  <h2 class="text-4xl font-extrabold mb-3"><?= esc($it['title'] ?? '-') ?></h2>
                  <?php if (!empty($it['excerpt'])): ?>
                    <p class="text-gray-700 leading-relaxed mb-2"><?= esc($it['excerpt']) ?></p>
                  <?php elseif (!empty($it['body'])): ?>
                    <p class="text-gray-700 leading-relaxed mb-2"><?= esc(strip_tags(mb_substr($it['body'], 0, 140))) ?>...</p>
                  <?php else: ?>
                    <p class="text-gray-700 leading-relaxed mb-2"><?= esc($settings['hero_tagline'] ?? '') ?></p>
                  <?php endif; ?>
                  <time class="text-sm text-gray-500"><?= esc(fmtDate($it['published_at'] ?? null)) ?></time>
                </div>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <article class="min-w-full p-10 flex flex-col md:flex-row items-center gap-10">
            <img src="<?= base_url('images/kantor.jpg') ?>" alt="Kantor"
              class="w-72 h-72 object-cover rounded-md shadow-md" onerror="this.style.display='none'" />
            <div class="max-w-xl">
              <p class="uppercase tracking-widest text-gray-500 mb-2">Selamat Datang</p>
              <h2 class="text-4xl font-extrabold mb-3"><?= esc($settings['hero_title'] ?? 'Kantor Notaris') ?></h2>
              <p class="text-gray-700 leading-relaxed">
                <?= esc($settings['hero_tagline'] ?? 'Profesional & terpercaya.') ?></p>
            </div>
          </article>
        <?php endif; ?>
      </div>
    </div>

    <button id="prevBtn"
      class="arrow-btn absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-4xl font-bold z-20 select-none">❮</button>
    <button id="nextBtn"
      class="arrow-btn absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-4xl font-bold z-20 select-none">❯</button>

    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex space-x-4 z-20">
      <?php for ($i = 0; $i < $dotCount; $i++): ?>
        <button class="w-4 h-4 rounded-full bg-gray-400"></button>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="container mx-auto px-6 py-12">
  <div class="flex items-center justify-between mb-8">
    <h2 class="text-3xl font-extrabold">Berita Terbaru Kantor Notaris</h2>
    <button id="newsLoadMore"
      class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition hidden">Berita
      lainnya</button>
  </div>

  <div id="newsGrid" class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <?php if (!empty($latest)): ?>
      <?php $i = 0;
      foreach ($latest as $it):
        if (++$i > 6)
          break; ?>
        <article class="news-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
          data-id="<?= isset($it['id']) ? (int) $it['id'] : 0 ?>">
          <img src="<?= esc(newsImageUrl($it['image'] ?? null)) ?>" alt="<?= esc($it['title'] ?? 'Berita') ?>"
            class="rounded-md mb-4 object-cover h-48 w-full" onerror="this.style.display='none'" />
          <h3 class="text-xl font-bold mb-2"><?= esc($it['title'] ?? '-') ?></h3>
          <?php if (!empty($it['excerpt'])): ?>
            <p class="text-gray-700 mb-3"><?= esc($it['excerpt']) ?></p>
          <?php elseif (!empty($it['body'])): ?>
            <p class="text-gray-700 mb-3"><?= esc(strip_tags(mb_substr($it['body'], 0, 120))) ?>...</p>
          <?php endif; ?>
          <time class="text-sm text-gray-500"><?= esc(fmtDate($it['published_at'] ?? null)) ?></time>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<section class="py-16 bg-gray-100">
  <div class="max-w-7xl mx-auto px-4 md:px-8 grid md:grid-cols-2 gap-8">
    <div class="bg-white rounded-2xl shadow-xl p-8 flex items-center space-x-6" data-aos="fade-up" data-aos-delay="100">
      <img src="<?= esc(ownerImageUrl($settings['owner_photo'] ?? null)) ?>" alt="Pemilik Notaris"
        class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover shadow-md">
      <div>
        <h2 class="text-3xl font-semibold text-gray-800 mb-2"><?= esc($settings['owner_name'] ?? 'Nama Pemilik') ?></h2>
        <p class="text-gray-600 leading-relaxed text-sm md:text-base">
          <?= esc($settings['owner_subtitle'] ?? 'Profil singkat pemilik atau jabatan.') ?></p>
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
        <h2 class="text-3xl font-semibold text-gray-800 mb-2"><?= esc($settings['about_title'] ?? 'Visi & Misi') ?></h2>
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
        <a href="mailto:<?= esc($settings['social_email']) ?>" class="hover:text-blue-600 transition" title="Email"><i
            class="fas fa-envelope"></i></a>
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
          width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
          referrerpolicy="no-referrer-when-downgrade" class="w-full rounded-xl"></iframe>
      <?php endif; ?>
    </div>
  </div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script> AOS.init({ duration: 1000, once: true }); </script>

<script>
  const slidesContainer = document.getElementById("slidesContainer");
  const slides = slidesContainer.children;
  const dots = document.querySelectorAll("#slider .absolute.bottom-5 button");
  let currentIndex = 0;
  const totalSlides = slides.length;
  function updateSlider(index) {
    if (index < 0) index = totalSlides - 1;
    else if (index >= totalSlides) index = 0;
    currentIndex = index;
    slidesContainer.style.transform = `translateX(-${index * 100}%)`;
    dots.forEach((dot, i) => dot.classList.toggle('bg-yellow-600', i === index));
  }
  document.getElementById("prevBtn").addEventListener("click", () => updateSlider(currentIndex - 1));
  document.getElementById("nextBtn").addEventListener("click", () => updateSlider(currentIndex + 1));
  dots.forEach((dot, idx) => dot.addEventListener('click', () => updateSlider(idx)));
  if (totalSlides > 1) setInterval(() => updateSlider(currentIndex + 1), 7000);
  updateSlider(0);
</script>

<script>
  (function () {
    const grid = document.getElementById('newsGrid');
    const btnMore = document.getElementById('newsLoadMore');
    const modal = document.getElementById('newsModal');
    const modalBody = document.getElementById('newsModalBody');

    const feedUrl = "<?= base_url('index.php/news/feed') ?>";
    const showUrl = id => "<?= base_url('index.php/news/show') ?>/" + id;

    function fmtID(s) {
      if (!s) return '';
      const d = new Date((s + '').replace(' ', 'T'));
      return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function cardTemplate(it) {
      const img = it.image ? `<img src="${it.image}" alt="${it.title}" class="rounded-md mb-4 object-cover h-48 w-full" onerror="this.style.display='none'">` : '';
      const plain = (it.excerpt && it.excerpt.trim().length) ? it.excerpt : (it.body || '').replace(/<[^>]+>/g, '');
      const preview = plain.length > 120 ? plain.slice(0, 120) + '…' : plain;
      return `
      <article class="news-card bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer" data-id="${it.id}">
        ${img}
        <h3 class="text-xl font-bold mb-2">${it.title}</h3>
        <p class="text-gray-700 mb-3">${preview}</p>
        <time class="text-sm text-gray-500">${fmtID(it.published_at)}</time>
      </article>`;
    }

    function openModal(html) { modalBody.innerHTML = html; modal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
    function closeModal() { modal.classList.add('hidden'); document.body.style.overflow = ''; }

    grid.querySelectorAll('.news-card').forEach(el => {
      const id = el.dataset.id;
      if (id && Number(id) > 0) el.addEventListener('click', () => openDetail(id));
    });

    let page = 1, loading = false, initializedFromAjax = false;

    async function loadPage() {
      if (loading) return; loading = true; if (btnMore) btnMore.disabled = true;
      try {
        const res = await fetch(`${feedUrl}?page=${page}`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('feed ' + res.status);
        const data = await res.json();

        if (page === 1 && grid.children.length > 0 && !initializedFromAjax) { grid.innerHTML = ''; }
        initializedFromAjax = true;

        (data.items || []).forEach(it => {
          grid.insertAdjacentHTML('beforeend', cardTemplate(it));
          const el = grid.lastElementChild;
          el.addEventListener('click', () => openDetail(it.id));
        });

        if (data.has_more) { btnMore.classList.remove('hidden'); btnMore.disabled = false; page += 1; }
        else { btnMore.classList.add('hidden'); }
      } catch (e) { console.error(e); } finally { loading = false; }
    }

    async function openDetail(id) {
      if (!id) return;
      try {
        const url = showUrl(id);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const ct = (res.headers.get('content-type') || '').toLowerCase();
        if (!res.ok || !ct.includes('application/json')) {
          const prev = await res.text().catch(() => '');
          console.error('News detail error:', res.status, url, ct, prev.slice(0, 200));
          alert('Tidak dapat membuka berita (kode ' + res.status + ').');
          return;
        }
        const n = await res.json();
        const img = n.image ? `<img src="${n.image}" alt="${n.title}" class="w-full max-h-80 object-cover rounded-lg mb-4">` : '';
        openModal(`
          <h3 class="text-2xl font-bold mb-2">${n.title}</h3>
          <div class="text-sm text-gray-500 mb-4">${fmtID(n.published_at)}</div>
          ${img}
          <div class="prose max-w-none">${n.body || ''}</div>
        `);
      } catch (e) {
        console.error(e);
        alert('Tidak dapat membuka berita (jaringan).');
      }
    }

    loadPage();
    btnMore && btnMore.addEventListener('click', loadPage);
    document.getElementById('newsModalClose').addEventListener('click', closeModal);
    document.getElementById('newsModal').addEventListener('click', (e) => { if (e.target.id === 'newsModal') closeModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
  })();
</script>

<div id="newsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
  <div class="absolute inset-0 bg-black bg-opacity-50"></div>
  <div class="relative bg-white w-11/12 max-w-3xl max-h-[85vh] overflow-auto rounded-xl shadow-xl p-6">
    <button id="newsModalClose"
      class="absolute top-3 right-3 w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center"
      aria-label="Tutup">✕</button>
    <div id="newsModalBody"></div>
  </div>
</div>

<?= $this->endSection() ?>