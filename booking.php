<?php
// Pastikan file ini diakses melalui index.php atau memiliki sesi aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

$pesan = '';
$tipe_pesan = '';

// Proses Simpan Reservasi / Booking Baru
if (isset($_POST['simpan_booking'])) {
    $nama_pemesan      = mysqli_real_escape_string($koneksi, $_POST['nama_pemesan']);
    $zone_parkir       = mysqli_real_escape_string($koneksi, $_POST['zone_parkir']);
    $tanggal_kunjungan = mysqli_real_escape_string($koneksi, $_POST['tanggal_kunjungan']);
    $plat_nomor        = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $jenis_kendaraan   = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $warna_kendaraan   = mysqli_real_escape_string($koneksi, $_POST['warna_kendaraan']);

    // Generate Kode Booking Unik
    $kode_booking      = 'SKE-' . strtoupper(substr(md5(time()), 0, 6));

    $query = "INSERT INTO tb_booking (nama_pemesan, zone_parkir, tanggal_kunjungan, plat_nomor, jenis_kendaraan, warna_kendaraan) 
              VALUES ('$nama_pemesan', '$zone_parkir', '$tanggal_kunjungan', '$plat_nomor', '$jenis_kendaraan', '$warna_kendaraan')";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan = "Reservasi berhasil! Kode Booking: <strong>$kode_booking</strong>";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal menyimpan reservasi: " . mysqli_error($koneksi);
        $tipe_pesan = "danger";
    }
}
?>

<div class="content-header" style="margin-bottom: 25px;">
    <h2 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 26px;">Reservasi Slot Parkir</h2>
    <p style="color: var(--text-muted); font-size: 13px;">Daftarkan kendaraan pengunjung untuk mengamankan slot parkir area wisata.</p>
</div>

<?php if (!empty($pesan)): ?>
    <div style="padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; background: <?= ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.15)' : 'rgba(231, 76, 60, 0.15)'; ?>; border: 1px solid <?= ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.4)' : 'rgba(231, 76, 60, 0.4)'; ?>; color: <?= ($tipe_pesan == 'success') ? '#2ecc71' : '#e74c3c'; ?>;">
        <?= $pesan; ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 25px;">
    
    <!-- Form Reservasi Slot Parkir -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 30px; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
        <div style="text-align: center; margin-bottom: 25px;">
            <h3 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 20px; margin-bottom: 5px;">
                <i class="fa-solid fa-calendar-days me-2"></i> Reservasi Slot Parkir
            </h3>
            <p style="color: var(--text-muted); font-size: 12px;">Daftarkan kendaraan pengunjung dalam satu sistem booking.</p>
        </div>
        
        <form method="POST" action="">
            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Nama Pemilik / Pengunjung</label>
                <input type="text" name="nama_pemesan" class="form-control" placeholder="Masukkan nama lengkap" style="width: 100%; padding: 12px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; color: #fff; font-size: 13px;" required>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Pilih Zone Parkir</label>
                <select name="zone_parkir" class="form-control" style="width: 100%; padding: 12px; background: rgba(15, 16, 21, 0.9); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; color: #fff; font-size: 13px;" required>
                    <option value="VIP Waterpark Zone">VIP Waterpark Zone</option>
                    <option value="Reguler Utama Zone">Reguler Utama Zone</option>
                    <option value="Bus & Minibus Zone">Bus & Minibus Zone</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px;">Tanggal Rencana Kunjungan</label>
                <input type="date" name="tanggal_kunjungan" class="form-control" style="width: 100%; padding: 12px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; color: #fff; font-size: 13px;" required>
            </div>

            <!-- Card Daftar Kendaraan #1 -->
            <div style="background: rgba(15, 16, 21, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span style="color: var(--gold-light); font-weight: 600; font-size: 13px;"><i class="fa-solid fa-car me-2"></i> Kendaraan #1</span>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Nomor Plat Kendaraan</label>
                    <input type="text" name="plat_nomor" class="form-control" placeholder="CONTOH: AB 1234 CD" style="width: 100%; padding: 11px; background: rgba(15, 16, 21, 0.8); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Jenis Kendaraan</label>
                        <select name="jenis_kendaraan" class="form-control" style="width: 100%; padding: 11px; background: rgba(15, 16, 21, 0.9); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
                            <option value="Motor">Motor</option>
                            <option value="Mobil Pribadi">Mobil Pribadi</option>
                            <option value="Bus/Minibus">Bus / Minibus</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Warna Kendaraan</label>
                        <input type="text" name="warna_kendaraan" class="form-control" placeholder="Hitam/Putih" style="width: 100%; padding: 11px; background: rgba(15, 16, 21, 0.8); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;">
                    </div>
                </div>
            </div>

            <button type="submit" name="simpan_booking" style="width: 100%; padding: 13px; background: linear-gradient(135deg, #d4af37, #aa8c2c); color: #0f1015; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; text-transform: uppercase; font-size: 13px; box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4); display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fa-solid fa-qrcode"></i> Dapatkan Kode Booking
            </button>
        </form>
    </div>

    <!-- Tabel Daftar Data Booking / Reservasi -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 30px; border-radius: 16px; height: fit-content;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; margin-bottom: 20px;">
            <i class="fa-solid fa-list me-2"></i> Daftar Data Reservasi
        </h4>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-gold); color: var(--gold-light);">
                        <th style="padding: 10px;">Pengunjung / Plat</th>
                        <th style="padding: 10px;">Zone</th>
                        <th style="padding: 10px; white-space: nowrap;">Kendaraan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_booking = mysqli_query($koneksi, "SELECT * FROM tb_booking");
                    if ($sql_booking && mysqli_num_rows($sql_booking) > 0) {
                        while ($row = mysqli_fetch_assoc($sql_booking)) {
                            $zone = $row['zone_parkir'] ?? '-';
                            $jenis = $row['jenis_kendaraan'] ?? '-';
                            
                            echo "<tr style='border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--text-muted);'>";
                            echo "<td style='padding: 12px;'><strong>{$row['nama_pemesan']}</strong><br><span style='color: var(--gold-light); font-size: 11px;'>{$row['plat_nomor']}</span></td>";
                            echo "<td style='padding: 12px; font-size: 12px;'>{$zone}</td>";
                            echo "<td style='padding: 12px;'>{$jenis}</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='padding: 20px; text-align: center; color: var(--text-muted);'>Belum ada data reservasi aktif.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>