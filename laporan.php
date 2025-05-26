<?php
include('config.php');
$transaksi = $conn->query('
    SELECT t.id, t.tanggal, t.tanggal_jadwal, v.pemilik, v.merk, v.tipe, v.plat_nomor, t.total_biaya
    FROM transaksi t
    JOIN kendaraan v ON v.id = t.kendaraan_id
    ORDER BY t.tanggal DESC
');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Service</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Beranda</a>
        <a href="register_layanan.php">Pendaftaran Service</a>
        <a href="schedule_layanan.php">Penjadwalan Service</a>
    </nav>
    <div class="container">
        <h1>Laporan Layanan Service</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Tgl Daftar</th>
                <th>Tgl Service</th>
                <th>Pemilik</th>
                <th>Kendaraan</th>
                <th>Total Biaya</th>
            </tr>
            <?php while($row = $transaksi->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['tanggal'] ?></td>
                    <td><?= $row['tanggal_jadwal'] ?? '-' ?></td>
                    <td><?= $row['pemilik'] ?></td>
                    <td><?= $row['merk'] . ' ' . $row['tipe'] ?> (<?= $row['plat_nomor'] ?>)</td>
                    <td>Rp<?= number_format($row['total_biaya'],0,',','.') ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <script src="script.js"></script>
</body>
</html>
