<?php
$h = static fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$bln = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$judulBulan = ($bln[(int) ($bulan ?? 0)] ?? $bulan) . ' ' . (string) $tahun;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan PPAT - <?= $h($judulBulan) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 6px 0;
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
            padding: 3.5px 4px;
            vertical-align: top;
            line-height: 1.12;
            word-break: break-word;
            /* default: patahkan kata panjang */
            white-space: normal;
        }

        /* angka panjang: pecah di mana saja (dompdf-friendly) */
        .num {
            word-break: break-all;
        }

        /* tanggal biar 1 baris */
        .date {
            white-space: nowrap;
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

        .th-2line span {
            display: block;
        }

        .tight td {
            padding-top: 3px;
            padding-bottom: 3px;
        }

        /* Lebar kolom — disetel agar muat A4 landscape */
        col.no {
            width: 28px;
        }

        col.nobul {
            width: 58px;
        }

        col.aktaNo {
            width: 64px;
        }

        col.aktaTgl {
            width: 68px;
        }

        col.bentuk {
            width: 72px;
        }

        col.nama {
            width: 112px;
        }

        col.jenis {
            width: 60px;
        }

        col.nomor {
            width: 74px;
        }

        col.letak {
            width: 136px;
        }

        col.luas {
            width: 50px;
        }

        col.nilai {
            width: 84px;
        }

        col.tahun {
            width: 52px;
        }

        col.ket {
            width: 108px;
        }
    </style>
</head>

<body>
    <h2>Laporan Bulanan — PPAT</h2>
    <div class="sub">Periode: <strong><?= $h($judulBulan) ?></strong></div>

    <table>
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
            <?php if (!empty($ppat)):
                foreach ($ppat as $r):
                    $p = (array) ($r['payload'] ?? []); ?>
                    <tr class="tight">
                        <td class="center"><?= $h($r['row_no'] ?? '') ?></td>
                        <td class="center num"><?= $h($r['nomor_bulanan'] ?? '') ?></td>

                        <td class="center num"><?= $h($p['akta_no'] ?? '') ?></td>
                        <td class="center date"><?= $h($p['akta_tgl'] ?? '') ?></td>

                        <td><?= $h($p['bentuk'] ?? '') ?></td>
                        <td><?= $h($p['pihak_pengalih'] ?? '') ?></td>
                        <td><?= $h($p['pihak_penerima'] ?? '') ?></td>

                        <td class="center"><?= $h($p['jenis_hak'] ?? '') ?></td>
                        <td class="center num"><?= $h($p['nomor_hak'] ?? '') ?></td>

                        <td><?= $h($p['letak'] ?? '') ?></td>

                        <td class="right num"><?= $h($p['luas_tnh'] ?? '') ?></td>
                        <td class="right num"><?= $h($p['luas_bgn'] ?? '') ?></td>

                        <td class="right num"><?= $h($p['nilai_transaksi'] ?? '') ?></td>

                        <td class="center num"><?= $h($p['sspt_nop'] ?? '') ?></td>
                        <td class="center num"><?= $h($p['sspt_tahun'] ?? '') ?></td>

                        <td class="right num"><?= $h($p['njop'] ?? '') ?></td>

                        <td class="right num"><?= $h($p['sep_nilai'] ?? '') ?></td>
                        <td class="center date"><?= $h($p['sep_tgl'] ?? '') ?></td>

                        <td class="right num"><?= $h($p['bphtb_nilai'] ?? '') ?></td>
                        <td><?= $h($p['ket'] ?? '') ?></td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="20" class="center small">Tidak ada data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="small" style="margin-top:6px">Catatan: Lampiran file tidak termasuk dalam PDF.</p>
</body>

</html>