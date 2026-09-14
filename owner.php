<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Validasi ketat: Hanya Owner dan Administrator yang boleh akses halaman ini
if (!isset($_SESSION['id_user']) || ($_SESSION['level'] != 'Owner' && $_SESSION['level'] != 'Administrator')) {
    echo "<div style='color: #e74c3c; padding: 20px;'>Akses ditolak! Halaman ini khusus untuk Owner dan Administrator.</div>";
    exit();
}

include 'koneksi.php';

// Hitung total pendapatan dan statistik dari tabel tb_pembayaran (jika ada)
$total_pendapatan = 0;
$total_transaksi = 0;
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tb_pembayaran'");
if (mysqli_num_rows($cek_tabel) > 0) {
    $sql_sum = mysqli_query($koneksi, "SELECT SUM(biaya) as total, COUNT(*) as jumlah FROM tb_pembayaran");
    $data_sum = mysqli_fetch_assoc($sql_sum);
    $total_pendapatan = $data_sum['total'] ?? 0;
    $total_transaksi = $data_sum['jumlah'] ?? 0;
}
?>

<div class="content-header" style="margin-bottom: 25px;">
    <h2 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 26px;">Laporan Keuangan & Data Owner</h2>
    <p style="color: var(--text-muted); font-size: 13px;">Ringkasan laporan dan rekapitulasi pendapatan sistem parkir Sindu Kusuma Edupark (SKE).</p>
</div>

<!-- Kartu Statistik Pendapatan -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 20px; border-radius: 14px;">
        <h4 style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; margin-bottom: 8px;"><i class="fa-solid fa-wallet me-2"></i> Total Pendapatan Parkir</h4>
        <div style="font-size: 24px; font-weight: 700; color: var(--gold-light);">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></div>
    </div>
    
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 20px; border-radius: 14px;">
        <h4 style="color: var(--text-muted); font-size: 11px; text-transform: uppercase; margin-bottom: 8px;"><i class="fa-solid fa-receipt me-2"></i> Total Transaksi Keluar</h4>
        <div style="font-size: 24px; font-weight: 700; color: #2ecc71;"><?= number_format($total_transaksi, 0, ',', '.'); ?> Tiket</div>
    </div>
</div>

<!-- Tabel Laporan Rekapitulasi Detail -->
<div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 25px; border-radius: 14px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px;">
            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Rekapitulasi Seluruh Transaksi Kasir
        </h4>
        <button onclick="window.print()" style="padding: 8px 15px; background: rgba(212, 175, 55, 0.15); border: 1px solid var(--border-gold); color: var(--gold-light); border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">
            <i class="fa-solid fa-print me-1"></i> Cetak Laporan
        </button>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-gold); color: var(--gold-light);">
                    <th style="padding: 10px;">Waktu Transaksi</th>
                    <th style="padding: 10px;">Plat Nomor</th>
                    <th style="padding: 10px;">Jenis Kendaraan</th>
                    <th style="padding: 10px;">Biaya Parkir</th>
                    <th style="padding: 10px;">Petugas Kasir</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($cek_tabel) > 0) {
                    $sql_laporan = mysqli_query($koneksi, "SELECT * FROM tb_pembayaran ORDER BY id_pembayaran DESC");
                    if (mysqli_num_rows($sql_laporan) > 0) {
                        while ($row = mysqli_fetch_assoc($sql_laporan)) {
                            echo "<tr style='border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--text-muted);'>";
                            echo "<td style='padding: 10px;'>{$row['tanggal']}</td>";
                            echo "<td style='padding: 10px; color: #fff; font-weight: 600;'>{$row['plat_nomor']}</td>";
                            echo "<td style='padding: 10px;'>{$row['jenis_kendaraan']}</td>";
                            echo "<td style='padding: 10px; color: var(--gold-light);'>Rp " . number_format($row['biaya'], 0, ',', '.') . "</td>";
                            echo "<td style='padding: 10px;'>{$row['petugas']}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='padding: 15px; text-align: center; color: var(--text-muted);'>Belum ada data laporan transaksi.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='padding: 15px; text-align: center; color: #e74c3c;'>Tabel `tb_pembayaran` belum tersedia di database.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>