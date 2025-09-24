<?php
// Variabel yang tersedia: $bulan, $tahun, $notaris, $ppat
$h = static fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

$bln = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$judulBulan = ($bln[(int) ($bulan ?? 0)] ?? $bulan) . ' ' . (string) $tahun;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan (Notaris & PPAT) - <?= $h($judulBulan) ?></title>
    <style>
        /* Halaman & margin (dompdf/mpdf) */
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            /* hemat ruang */
            color: #111;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 8px 0;
        }

        .section-title {
            margin-top: 12px;
        }

        .sub {
            color: #555;
            font-size: 11px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 4px 5px;
            /* rapat */
            vertical-align: top;
            line-height: 1.15;
            word-break: break-word;
            /* cegah tumpah */
            overflow-wrap: anywhere;
            white-space: normal;
            hyphens: auto;
        }

        thead th {
            background: #efefef;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .small {
            font-size: 10px;
            color: #555;
        }

        .nowrap {
            white-space: nowrap;
        }

        /* NOTARIS: kolom sederhana, cukup fixed */
        .notaris col.nobul {
            width: 70px;
        }

        .notaris col.tgl {
            width: 80px;
        }

        .notaris col.sifat {
            width: 120px;
        }

        .notaris col.nama {
            width: auto;
        }

        /* PPAT: kolom diukur supaya pas A4 landscape */
        table.ppat {
            font-size: 10.5px;
        }

        table.ppat col.no {
            width: 30px;
        }

        table.ppat col.nobul {
            width: 58px;
        }

        table.ppat col.aktaNo {
            width: 70px;
        }

        table.ppat col.aktaTgl {
            width: 70px;
        }

        table.ppat col.bentuk {
            width: 84px;
        }

        table.ppat col.nama {
            width: 120px;
        }

        table.ppat col.jenis {
            width: 66px;
        }

        table.ppat col.nomor {
            width: 78px;
        }

        table.ppat col.letak {
            width: 140px;
        }

        table.ppat col.luas {
            width: 55px;
        }

        table.ppat col.nilai {
            width: 90px;
        }

        table.ppat col.tahun {
            width: 54px;
        }

        table.ppat col.ket {
            width: 120px;
        }

        /* Header dua baris lebih rapi */
        .th-2line span {
            display: block;
        }

        .tight td {
            padding-top: 3.5px;
            padding-bottom: 3.5px;
        }

        .break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    <!-- ====== NOTARIS ====== -->
    <h2>Laporan Bulanan — NOTARIS</h2>
    <div class="sub">Periode: <strong><?= $h($judulBulan) ?></strong></div>

    <table class="notaris">
        <colgroup>
            <col class="nobul">
            <col class="tgl">
            <col class="sifat">
            <col class="nama">
        </colgroup>
        <thead>
            <tr>
                <th class="center nowrap">No Bulan</th>
                <th class="center nowrap">Tanggal</th>
                <th class="center">Sifat</th>
                <th class="center">Nama Penghadap dan/atau yang diwakili/kuasa</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($notaris)): ?>
                <?php foreach ($notaris as $r): ?>
                    <?php $p = (array) ($r['payload'] ?? []); ?>
                    <tr class="tight">
                        <td class="center"><?= $h($r['nomor_bulanan'] ?? '') ?></td>
                        <td class="center nowrap"><?= $h(($p['tanggal'] ?? $r['tanggal'] ?? '')) ?></td>
                        <td><?= $h($p['sifat'] ?? '') ?></td>
                        <td><?= $h($p['nama_penghadap'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="center small">Tidak ada data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ====== PPAT (halaman baru) ====== -->
    <div class="break"></div>
    <h2 class="section-title">Laporan Bulanan — PPAT</h2>
    <div class="sub">Periode: <strong><?= $h($judulBulan) ?></strong></div>

    <table class="ppat">
        <colgroup>
            <col class="no">
            <col class="nobul">
            <col class="aktaNo">
            <col class="aktaTgl">
            <col class="bentuk">
            <col class="nama">
            <col class="nama">
            <col class="jenis">
            <col class="nomor">
            <col class="letak">
            <col class="luas">
            <col class="luas">
            <col class="nilai">
            <col class="nomor">
            <col class="tahun">
            <col class="nilai">
            <col class="nilai">
            <col class="aktaTgl">
            <col class="nilai">
            <col class="ket">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2" class="center">No</th>
                <th rowspan="2" class="center nowrap">No Bulanan</th>
                <th colspan="2" class="center">Akta</th>
                <th rowspan="2" class="center">Bentuk</th>
                <th colspan="2" class="center">Nama</th>
                <th colspan="2" class="center">Jenis &amp; Nomor Hak</th>
                <th rowspan="2" class="center th-2line"><span>Letak</span><span>Tanah &amp; Bangunan</span></th>
                <th colspan="2" class="center">Luas m²</th>
                <th rowspan="2" class="center th-2line"><span>Nilai</span><span>Transaksi</span></th>
                <th colspan="2" class="center">SSPT PBB</th>
                <th rowspan="2" class="center">NJOP</th>
                <th colspan="2" class="center">SEP</th>
                <th rowspan="2" class="center th-2line"><span>BPHTB</span><span>Nilai</span></th>
                <th rowspan="2" class="center">KET</th>
            </tr>
            <tr>
                <th class="center">No</th>
                <th class="center">Tanggal</th>
                <th class="center th-2line"><span>Pihak</span><span>Mengalihkan/Memberi</span></th>
                <th class="center th-2line"><span>Pihak</span><span>Menerima</span></th>
                <th class="center">Jenis</th>
                <th class="center">Nomor</th>
                <th class="center">Tnh</th>
                <th class="center">Bgn</th>
                <th class="center">NOP</th>
                <th class="center">Tahun</th>
                <th class="center">Nilai</th>
                <th class="center">Tggl</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($ppat)): ?>
                <?php foreach ($ppat as $r): ?>
                    <?php $p = (array) ($r['payload'] ?? []); ?>
                    <tr class="tight">
                        <td class="center"><?= $h($r['row_no'] ?? '') ?></td>
                        <td class="center"><?= $h($r['nomor_bulanan'] ?? '') ?></td>
                        <td class="center"><?= $h($p['akta_no'] ?? '') ?></td>
                        <td class="center nowrap"><?= $h($p['akta_tgl'] ?? '') ?></td>
                        <td><?= $h($p['bentuk'] ?? '') ?></td>
                        <td><?= $h($p['pihak_pengalih'] ?? '') ?></td>
                        <td><?= $h($p['pihak_penerima'] ?? '') ?></td>
                        <td class="center"><?= $h($p['jenis_hak'] ?? '') ?></td>
                        <td class="center"><?= $h($p['nomor_hak'] ?? '') ?></td>
                        <td><?= $h($p['letak'] ?? '') ?></td>
                        <td class="right"><?= $h($p['luas_tnh'] ?? '') ?></td>
                        <td class="right"><?= $h($p['luas_bgn'] ?? '') ?></td>
                        <td class="right"><?= $h($p['nilai_transaksi'] ?? '') ?></td>
                        <td class="center"><?= $h($p['sspt_nop'] ?? '') ?></td>
                        <td class="center"><?= $h($p['sspt_tahun'] ?? '') ?></td>
                        <td class="right"><?= $h($p['njop'] ?? '') ?></td>
                        <td class="right"><?= $h($p['sep_nilai'] ?? '') ?></td>
                        <td class="center nowrap"><?= $h($p['sep_tgl'] ?? '') ?></td>
                        <td class="right"><?= $h($p['bphtb_nilai'] ?? '') ?></td>
                        <td><?= $h($p['ket'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="20" class="center small">Tidak ada data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="small" style="margin-top:6px">Catatan: Lampiran file tidak termasuk dalam PDF.</p>
</body>

</html>