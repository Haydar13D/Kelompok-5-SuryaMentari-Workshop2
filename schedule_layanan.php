<?php 
// ============= schedule_layanan.php =============
include('config.php'); 
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = $_POST['transaction_id'] ?? '';
    $tanggal_jadwal = $_POST['tanggal_jadwal'] ?? '';
    
    if (empty($transaction_id) || empty($tanggal_jadwal)) {
        $message = 'Data tidak lengkap!';
    } else {
        try {
            $stmt = $conn->prepare('UPDATE transaksi SET tanggal_jadwal = ? WHERE id = ?');
            $stmt->bind_param('si', $tanggal_jadwal, $transaction_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $message = 'Penjadwalan berhasil!';
            } else {
                $message = 'Transaksi tidak ditemukan!';
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }
}

$pending = $conn->query('
    SELECT t.id, v.pemilik, v.merk, v.tipe, v.plat_nomor, t.total_biaya
    FROM transaksi t
    JOIN kendaraan v ON v.id = t.kendaraan_id
    WHERE t.tanggal_jadwal IS NULL
    ORDER BY t.tanggal ASC
');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Penjadwalan Service</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Beranda</a>
        <a href="register_layanan.php">Pendaftaran Service</a>
        <a href="laporan.php">Laporan Service</a>
    </nav>
    <div class="container">
        <h1>Penjadwalan Service</h1>
        <?php if ($message): ?>
            <p class="<?= strpos($message, 'Error') === 0 ? 'error' : 'success' ?> flash"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        
        <?php if ($pending && $pending->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Pemilik</th>
                    <th>Kendaraan</th>
                    <th>Total Biaya</th>
                    <th>Jadwalkan</th>
                </tr>
                <?php while($row = $pending->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['pemilik']) ?></td>
                        <td><?= htmlspecialchars($row['merk'] . ' ' . $row['tipe']) ?> (<?= htmlspecialchars($row['plat_nomor']) ?>)</td>
                        <td>Rp<?= number_format($row['total_biaya'],0,',','.') ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="transaction_id" value="<?= $row['id'] ?>">
                                <input type="datetime-local" name="tanggal_jadwal" required>
                                <button type="submit">Atur</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Tidak ada transaksi yang perlu dijadwalkan.</p>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
</body>
</html>