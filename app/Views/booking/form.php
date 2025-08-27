<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="max-w-xl mx-auto mt-10 p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-6 text-center">Form Booking</h2>

    <?= form_open('/booking/store') ?>

    <!-- Hidden input -->
    <input type="hidden" name="karyawan_id" value="<?= esc($karyawan_id) ?>">
    <input type="hidden" name="jadwal_id" value="<?= esc($jadwal_id) ?>">

    <!-- Nama Pegawai -->
    <div class="mb-4">
        <label for="nama_display" class="block text-sm font-medium text-gray-700 mb-1">Nama Pegawai:</label>
        <input type="text" id="nama_display" value="<?= esc($nama_karyawan) ?>"
            class="w-full border border-gray-300 rounded-md p-2 bg-gray-100 text-gray-700" disabled>

        <!-- Hidden field untuk dikirim ke server -->
        <input type="hidden" name="nama" value="<?= esc($nama_karyawan) ?>">
    </div>

    <div class="form-group">
        <label for="jam">Jam Konsultasi:</label>
        <input type="text" id="jam" name="jam" value="<?= esc($jam) ?>" readonly>
    </div>

    <!-- Keluhan -->
    <div class="mb-4">
        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Keluhan:</label>
        <textarea id="catatan" name="catatan" rows="4" placeholder="Masukkan keluhan anda..." required
            class="w-full border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
    </div>

    <!-- Submit Button -->
    <div class="text-center">
        <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-md transition duration-200">
            Kirim Booking
        </button>
    </div>

    <?= form_close() ?>
</div>

<?= $this->endSection() ?>