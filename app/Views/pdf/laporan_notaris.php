<?php
// $bulan, $tahun, $notaris, $ppat sudah dikirim dari controller::export()

$h = static fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
$bln = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$judulBulan = ($bln[(int) ($bulan ?? 0)] ?? $bulan) . ' ' . (string) $tahun;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan Notaris - <?= $h($judulBulan) ?></title>
    <style>
        * {
            box-sizing: border-box
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 6px 0;
        }

        .sub {
            color: #555;
            font-size: 12px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px 8px;
            vertical-align: top;
        }

        thead th {
            background: #efefef;
        }

        .right {
            text-align: right
        }

        .center {
            text-align: center
        }

        .small {
            font-size: 11px;
            color: #555;
        }

        .nowrap {
            white-space: nowrap
        }
    </style>
</head>

<body>
    <h2>Laporan Bulanan — NOTARIS</h2>
    <div class="sub">Periode: <strong><?= $h($judulBulan) ?></strong></div>

    <table>
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
                    <tr>
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

    <p class="small" style="margin-top:8px">Catatan: Lampiran file tidak termasuk dalam PDF.</p>
</body>

</html>