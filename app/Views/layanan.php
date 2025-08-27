<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// base url helper utk JS (hindari hardcode /)
$jadwalBase = rtrim(site_url('jadwal'), '/');
$bookingCreate = site_url('booking/create');
$todayPreset = isset($date) ? (string) $date : date('Y-m-d');
?>

<!-- Section Layanan Konsultasi -->
<section class="py-16 bg-gray-100">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Layanan Konsultasi</h2>

    <div id="employee-list"
        class="grid md:grid-cols-3 sm:grid-cols-2 grid-cols-1 gap-6 px-6 max-w-7xl mx-auto transition-all duration-300">
        <?php foreach ($employees as $emp): ?>
            <div class="employee-card bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition-all duration-500">
                <?php
                $foto = (string) ($emp['foto'] ?? '');
                $src = $foto !== '' ? base_url('images/karyawan/' . $foto) : 'https://via.placeholder.com/96?text=IMG';
                ?>
                <img src="<?= esc($src) ?>" alt="<?= esc($emp['nama']) ?>"
                    class="w-24 h-24 rounded-full mx-auto mb-4 object-cover" />
                <h3 class="text-lg font-semibold text-center"><?= esc($emp['nama']) ?></h3>
                <p class="text-sm text-gray-600 text-center"><?= esc($emp['jabatan'] ?? '-') ?></p>
                <?php if (!empty($emp['spesialisasi'])): ?>
                    <p class="text-xs text-gray-400 text-center italic">Spesialis: <?= esc($emp['spesialisasi']) ?></p>
                <?php endif; ?>
                <button onclick="openSchedule(<?= (int) $emp['id'] ?>)"
                    class="mt-4 block mx-auto bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition-all">
                    Lihat Jadwal
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Slide Panel Jadwal -->
<section class="container mx-auto px-6 py-12">
    <div id="schedule-panel" class="hidden fixed inset-0 bg-white z-50 p-6 overflow-auto transition-all">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-2xl font-bold text-gray-800">Jadwal Konsultasi</h3>
            <button onclick="closeSchedule()" class="text-red-500 hover:text-red-700 text-sm font-semibold">
                Tutup ✖
            </button>
        </div>

        <!-- Navigator Tanggal -->
        <div class="flex items-center gap-2 mb-4">
            <button id="btnPrev" class="px-3 py-2 rounded-lg border hover:bg-gray-50" type="button">←</button>

            <input id="datePicker" type="date" class="px-3 py-2 rounded-lg border" value="<?= esc($todayPreset) ?>">

            <button id="btnNext" class="px-3 py-2 rounded-lg border hover:bg-gray-50" type="button">→</button>

            <span id="dateNote" class="ml-3 text-sm text-gray-500"></span>
        </div>

        <div id="schedule-content" class="space-y-4">
            <!-- Konten jadwal dimuat via JS -->
        </div>
    </div>
</section>

<script>
    // ====== Utilities tanggal ======
    const tzOffsetFix = (d) => {
        // normalize to local date input (YYYY-MM-DD)
        const pad = n => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    };
    const addDays = (dateStr, n) => {
        const d = new Date(dateStr + 'T00:00:00');
        d.setDate(d.getDate() + n);
        return tzOffsetFix(d);
    };

    // ====== State ======
    let currentEmployeeId = null;
    const baseJadwal = "<?= $jadwalBase ?>";
    const bookingCreate = "<?= $bookingCreate ?>";

    // ====== Open / Close Panel ======
    function openSchedule(employeeId) {
        currentEmployeeId = employeeId;
        document.getElementById("employee-list").classList.add("blur-sm", "pointer-events-none");
        document.getElementById("schedule-panel").classList.remove("hidden");

        // preset tanggal dari server (hari ini) → #datePicker
        loadSchedule();
    }

    function closeSchedule() {
        document.getElementById("schedule-panel").classList.add("hidden");
        document.getElementById("employee-list").classList.remove("blur-sm", "pointer-events-none");
        document.getElementById("schedule-content").innerHTML = '';
        currentEmployeeId = null;
    }

    // ====== Load & render jadwal untuk TANGGAL TERPILIH ======
    async function loadSchedule() {
        if (!currentEmployeeId) return;

        const datePicker = document.getElementById('datePicker');
        const selectedDate = datePicker.value || "<?= $todayPreset ?>";
        document.getElementById('dateNote').textContent = `Menampilkan jadwal tanggal ${selectedDate}`;

        try {
            // Ambil semua jadwal pegawai ini (endpoint kamu: /jadwal/{employeeId})
            const res = await fetch(`${baseJadwal}/${currentEmployeeId}`);
            const all = await res.json();

            // Filter hanya yang TANGGAL = selectedDate
            const rows = (Array.isArray(all) ? all : []).filter(j => j.tanggal === selectedDate);

            const box = document.getElementById("schedule-content");
            box.innerHTML = '';

            if (!rows.length) {
                box.innerHTML =
                    `<div class="border p-4 rounded-lg bg-gray-50 text-gray-600">
             Belum ada slot untuk tanggal ini.
           </div>`;
                return;
            }

            rows.forEach(j => {
                const isAvail = String(j.status || '').toLowerCase() === 'available';
                const statusColor = isAvail ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';

                const button = isAvail
                    ? `<a href="${bookingCreate}?karyawan=${encodeURIComponent(currentEmployeeId)}&jadwal=${encodeURIComponent(j.id)}"
                class="mt-2 inline-block bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">
               Booking
             </a>`
                    : `<span class="mt-2 inline-block bg-gray-300 text-gray-600 px-4 py-1 rounded text-sm cursor-not-allowed">
               Booked
             </span>`;

                box.insertAdjacentHTML('beforeend', `
          <div class="border p-4 rounded-lg shadow bg-white flex items-center justify-between gap-4">
            <div>
              <div class="text-sm text-gray-500">Tanggal / Hari: ${j.tanggal}</div>
              <div class="font-medium text-gray-800">Jam: ${j.jam}</div>
              <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs ${statusColor}">
                ${j.status}
              </span>
              ${j.note ? `<div class="text-xs text-gray-500 mt-1">
                           <i class="fa-regular fa-circle-question"></i> ${j.note}
                          </div>` : ``}
            </div>
            <div>${button}</div>
          </div>
        `);
            });
        } catch (e) {
            console.error(e);
            document.getElementById("schedule-content").innerHTML =
                `<div class="border p-4 rounded-lg bg-red-50 text-red-700">
           Gagal memuat jadwal. Coba lagi.
         </div>`;
        }
    }

    // ====== Navigator tanggal ======
    document.getElementById('btnPrev').addEventListener('click', () => {
        const dp = document.getElementById('datePicker');
        dp.value = addDays(dp.value || "<?= $todayPreset ?>", -1);
        loadSchedule();
    });
    document.getElementById('btnNext').addEventListener('click', () => {
        const dp = document.getElementById('datePicker');
        dp.value = addDays(dp.value || "<?= $todayPreset ?>", 1);
        loadSchedule();
    });
    document.getElementById('datePicker').addEventListener('change', loadSchedule);
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?= $this->endSection() ?>