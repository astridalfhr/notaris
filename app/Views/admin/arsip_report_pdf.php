<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Surat Bulan <?= esc($month) ?></title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px
        }

        h2 {
            margin: 0 0 10px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top
        }

        th {
            background: #f5f5f5
        }
    </style>
</head>

<body>
    <h2>Laporan Surat Menyurat Bulan <?= esc($month) ?></h2>
    <table>
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th style="width:80px">Tanggal</th>
                <th style="width:70px">Jenis</th>
                <th>Nomor</th>
                <th>Perihal</th>
                <th>Pihak</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)):
                $i = 1;
                foreach ($rows as $r): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= esc($r['tanggal']) ?></td>
                        <td><?= strtoupper(esc($r['jenis'])) ?></td>
                        <td><?= esc($r['nomor_surat']) ?></td>
                        <td><?= esc($r['perihal']) ?></td>
                        <td><?= esc($r['pihak']) ?></td>
                    </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="6">Tidak ada data.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>