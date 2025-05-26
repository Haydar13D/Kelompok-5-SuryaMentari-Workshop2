<?php 
// ============= register_layanan.php =============
include('config.php'); 
$message = '';

// Fetch layanan types
$layanans = $conn->query('SELECT * FROM jenis_layanan');

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extract form data
    $pemilik = $_POST['pemilik'] ?? '';
    $merk = $_POST['merk'] ?? '';
    $tipe = $_POST['tipe'] ?? '';
    $plat_nomor = $_POST['plat_nomor'] ?? '';
    $layanan_ids = $_POST['layanans'] ?? [];
    
    // Validate required fields
    if (empty($pemilik) || empty($merk) || empty($tipe) || empty($plat_nomor) || empty($layanan_ids)) {
        $message = 'Semua field harus diisi!';
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // Insert kendaraan data first
            $kendaraan_stmt = $conn->prepare('INSERT INTO kendaraan (pemilik, merk, tipe, plat_nomor) VALUES (?, ?, ?, ?)');
            $kendaraan_stmt->bind_param('ssss', $pemilik, $merk, $tipe, $plat_nomor);
            $kendaraan_stmt->execute();
            $kendaraan_id = $kendaraan_stmt->insert_id;
            $kendaraan_stmt->close();
            
            // Calculate total cost
            $total_biaya = 0;
            if (!empty($layanan_ids)) {
                $placeholders = implode(',', array_fill(0, count($layanan_ids), '?'));
                $types = str_repeat('i', count($layanan_ids));
                
                // Prepare statement
                $sql = "SELECT SUM(biaya) AS total FROM jenis_layanan WHERE id IN ($placeholders)";
                $layanan_stmt = $conn->prepare($sql);
                
                // Bind parameters
                $layanan_stmt->bind_param($types, ...$layanan_ids);
                
                $layanan_stmt->execute();
                $result = $layanan_stmt->get_result();
                $row = $result->fetch_assoc();
                $total_biaya = $row['total'] ?? 0;
                $layanan_stmt->close();
            }
            
            // Insert transaction
            $tstmt = $conn->prepare('INSERT INTO transaksi (kendaraan_id, tanggal, total_biaya) VALUES (?, ?, ?)');
            $now = date('Y-m-d H:i:s');
            $tstmt->bind_param('isd', $kendaraan_id, $now, $total_biaya);
            $tstmt->execute();
            $transaction_id = $tstmt->insert_id;
            $tstmt->close();
            
            // Link layanans
            $link_stmt = $conn->prepare('INSERT INTO layanan_transaksi (transaction_id, layanan_id) VALUES (?, ?)');
            foreach ($layanan_ids as $layanan_id) {
                $link_stmt->bind_param('ii', $transaction_id, $layanan_id);
                $link_stmt->execute();
            }
            $link_stmt->close();
            
            // Commit transaction
            $conn->commit();
            $message = 'Pendaftaran berhasil!';
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $message = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Service</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href="index.php">Beranda</a>
        <a href="schedule_layanan.php">Penjadwalan Service</a>
        <a href="laporan.php">Laporan Service</a>
    </nav>
    <div class="container">
        <h1>Pendaftaran Service</h1>
        <?php if ($message): ?>
            <p class="<?= strpos($message, 'Error') === 0 ? 'error' : 'success' ?> flash"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <h3>Data Kendaraan</h3>
            <label>Nama Pemilik</label>
            <input type="text" name="pemilik" value="<?= htmlspecialchars($_POST['pemilik'] ?? '') ?>" required>
            
            <label>Merk</label>
            <input type="text" name="merk" value="<?= htmlspecialchars($_POST['merk'] ?? '') ?>" required>
            
            <label>Model</label>
            <input type="text" name="tipe" value="<?= htmlspecialchars($_POST['tipe'] ?? '') ?>" required>
            
            <label>Nomor Plat</label>
            <input type="text" name="plat_nomor" value="<?= htmlspecialchars($_POST['plat_nomor'] ?? '') ?>" required>

            <h3>Pilih Layanan</h3>
            <?php 
            // Reset result pointer for second loop
            $layanans->data_seek(0);
            while($row = $layanans->fetch_assoc()): 
                $checked = in_array($row['id'], $_POST['layanans'] ?? []) ? 'checked' : '';
            ?>
                <label>
                    <input type="checkbox" name="layanans[]" value="<?= $row['id'] ?>" <?= $checked ?>>
                    <?= htmlspecialchars($row['nama']) ?> (Rp<?= number_format($row['biaya'],0,',','.') ?>)
                </label><br>
            <?php endwhile; ?>

            <button type="submit">Daftar Service</button>
        </form>
    </div>
    <script src="script.js"></script>
</body>
</html>