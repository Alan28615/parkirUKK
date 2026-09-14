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

$found_data = null;
$durasi_jam = 1;
$total_biaya = 0;
$struk_data = null; 

// Jika tombol "Proses Keluar" pada tabel diklik
if (isset($_GET['aksi']) && $_GET['aksi'] == 'keluar' && isset($_GET['plat'])) {
    $plat_pilih = mysqli_real_escape_string($koneksi, $_GET['plat']);
    $query_cek = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan WHERE plat_nomor = '$plat_pilih' AND (status = 'Parkir' OR status = '')");
    if ($query_cek && mysqli_num_rows($query_cek) > 0) {
        $found_data = mysqli_fetch_assoc($query_cek);
        
        // Hitung durasi jam dari waktu masuk
        $waktu_masuk = (!empty($found_data['waktu_masuk'])) ? $found_data['waktu_masuk'] : date('Y-m-d H:i:s');
        $timestamp_masuk = strtotime($waktu_masuk);
        $timestamp_sekarang = time();
        
        $selisih_detik = $timestamp_sekarang - $timestamp_masuk;
        $durasi_jam = ceil($selisih_detik / 3600);
        if ($durasi_jam < 1) $durasi_jam = 1;
        
        // Tarif per jam
        $tarif_per_jam = 3000;
        $jenis = (isset($found_data['jenis_kendaraan'])) ? strtolower($found_data['jenis_kendaraan']) : 'motor';
        if (strpos($jenis, 'mobil') !== false) {
            $tarif_per_jam = 5000;
        } elseif (strpos($jenis, 'bus') !== false || strpos($jenis, 'elf') !== false) {
            $tarif_per_jam = 15000;
        }
        
        $total_biaya = $durasi_jam * $tarif_per_jam;
    }
}

// Proses Simpan Pembayaran Parkir
if (isset($_POST['proses_bayar'])) {
    $plat_nomor        = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $jenis_kendaraan   = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $durasi_jam        = intval($_POST['durasi_jam']);
    $total_biaya       = floatval($_POST['biaya']);
    $metode_pembayaran = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);
    
    $bayar             = (isset($_POST['bayar'])) ? floatval($_POST['bayar']) : $total_biaya;
    $kembalian         = 0;

    // Validasi apakah kendaraan masih aktif parkir
    $cek_status = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan WHERE plat_nomor = '$plat_nomor' AND (status = 'Parkir' OR status = '')");

    if (mysqli_num_rows($cek_status) == 0) {
        $pesan = "Transaksi ditolak! Kendaraan sudah keluar atau tidak valid.";
        $tipe_pesan = "danger";
    } elseif ($metode_pembayaran == 'Tunai' && $bayar < $total_biaya) {
        $pesan = "Uang pembayaran kurang dari total biaya!";
        $tipe_pesan = "danger";
    } else {
        if ($metode_pembayaran == 'Tunai') {
            $kembalian = $bayar - $total_biaya;
        } else {
            $bayar = $total_biaya;
            $kembalian = 0;
        }

        $tanggal = date('Y-m-d H:i:s');
        $kode_transaksi = 'TRX-' . time();
        $petugas = (isset($_SESSION['nama_user'])) ? $_SESSION['nama_user'] : 'Kasir';

        // 1. Simpan ke tabel tb_pembayaran
        $query = "INSERT INTO tb_pembayaran (kode_transaksi, plat_nomor, jenis_kendaraan, durasi_jam, total_biaya, metode_pembayaran, tanggal_transaksi) 
                  VALUES ('$kode_transaksi', '$plat_nomor', '$jenis_kendaraan', '$durasi_jam', '$total_biaya', '$metode_pembayaran', '$tanggal')";
        
        if (mysqli_query($koneksi, $query)) {
            // 2. Update status kendaraan di tb_kendaraan menjadi 'Selesai'
            $update_kendaraan = "UPDATE tb_kendaraan SET status = 'Selesai' WHERE plat_nomor = '$plat_nomor'";
            mysqli_query($koneksi, $update_kendaraan);

            $pesan = "Pembayaran via " . $metode_pembayaran . " berhasil diproses!";
            $tipe_pesan = "success";
            
            // Siapkan data struk
            $struk_data = array(
                'kode_transaksi' => $kode_transaksi,
                'plat_nomor' => $plat_nomor,
                'jenis_kendaraan' => $jenis_kendaraan,
                'durasi_jam' => $durasi_jam,
                'total_biaya' => $total_biaya,
                'metode_pembayaran' => $metode_pembayaran,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
                'tanggal' => $tanggal,
                'petugas' => $petugas
            );

            $found_data = null; 
            $total_biaya = 0;
            $durasi_jam = 1;
        } else {
            $pesan = "Gagal menyimpan transaksi: " . mysqli_error($koneksi);
            $tipe_pesan = "danger";
        }
    }
}
?>

<!-- Style khusus untuk print struk & kerapian tabel -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #area-struk, #area-struk * {
        visibility: visible;
    }
    #area-struk {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: #fff !important;
        color: #000 !important;
        padding: 20px;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<div class="content-header no-print" style="margin-bottom: 25px;">
    <h2 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 26px;">Pembayaran Kasir Parkir</h2>
    <p style="color: var(--text-muted); font-size: 13px;">Kelola transaksi masuk dan keluar kendaraan serta pencatatan karcis.</p>
</div>

<?php if (!empty($pesan)): ?>
    <div class="no-print" style="padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; background: <?php echo ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.15)' : 'rgba(231, 76, 60, 0.15)'; ?>; border: 1px solid <?php echo ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.4)' : 'rgba(231, 76, 60, 0.4)'; ?>; color: <?php echo ($tipe_pesan == 'success') ? '#2ecc71' : '#e74c3c'; ?>;">
        <?php echo $pesan; ?>
    </div>
<?php endif; ?>

<!-- Area Struk Pembayaran -->
<?php if ($struk_data): ?>
<div id="area-struk" style="background: var(--bg-card); border: 1px dashed var(--border-gold); padding: 25px; border-radius: 14px; margin-bottom: 30px; max-width: 500px; margin-left: auto; margin-right: auto;">
    <div style="text-align: center; border-bottom: 1px dashed rgba(255,255,255,0.2); padding-bottom: 15px; margin-bottom: 15px;">
        <h3 style="font-family: 'Playfair Display', serif; color: var(--gold-light); margin: 0; font-size: 20px;">SINDHUKUSUMA EDUPARK</h3>
        <p style="color: var(--text-muted); font-size: 11px; margin: 5px 0 0 0;">Struk Resmi Pembayaran Parkir Kendaraan</p>
    </div>
    
    <div style="font-size: 13px; color: #fff; display: flex; flex-direction: column; gap: 8px;">
        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">No. Transaksi:</span> <strong><?php echo $struk_data['kode_transaksi']; ?></strong></div>
        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Tanggal:</span> <span><?php echo $struk_data['tanggal']; ?></span></div>
        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Plat Nomor:</span> <strong style="color: var(--gold-light);"><?php echo $struk_data['plat_nomor']; ?></strong></div>
        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Jenis Kendaraan:</span> <span><?php echo $struk_data['jenis_kendaraan']; ?></span></div>
        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Durasi Parkir:</span> <span><?php echo $struk_data['durasi_jam']; ?> Jam</span></div>
        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Metode Bayar:</span> <strong style="color: var(--gold-light);"><?php echo $struk_data['metode_pembayaran']; ?></strong></div>
        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Total Biaya:</span> <strong>Rp <?php echo number_format($struk_data['total_biaya'], 0, ',', '.'); ?></strong></div>
        
        <?php if ($struk_data['metode_pembayaran'] == 'Tunai'): ?>
            <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Tunai Diterima:</span> <span>Rp <?php echo number_format($struk_data['bayar'], 0, ',', '.'); ?></span></div>
            <div style="display: flex; justify-content: space-between; border-top: 1px dashed rgba(255,255,255,0.2);"><span style="color: var(--text-muted);">Kembalian:</span> <strong style="color: #2ecc71;">Rp <?php echo number_format($struk_data['kembalian'], 0, ',', '.'); ?></strong></div>
        <?php else: ?>
            <div style="display: flex; justify-content: space-between; border-top: 1px dashed rgba(255,255,255,0.2);"><span style="color: var(--text-muted);">Status QRIS:</span> <strong style="color: #2ecc71;">Lunas / Berhasil</strong></div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between;"><span style="color: var(--text-muted);">Petugas Kasir:</span> <span><?php echo $struk_data['petugas']; ?></span></div>
    </div>

    <div style="text-align: center; margin-top: 20px; display: flex; gap: 10px;" class="no-print">
        <button onclick="window.print()" style="flex: 1; padding: 10px; background: #3498db; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 12px;">
            <i class="fa-solid fa-print me-1"></i> Cetak Struk
        </button>
        <a href="index.php?page=pembayaran" style="flex: 1; padding: 10px; background: rgba(255,255,255,0.1); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 12px; text-align: center; line-height: 20px;">
            Selesai / Tutup
        </a>
    </div>
</div>
<?php endif; ?>

<div class="no-print" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 25px;">
    
    <!-- Form Kasir Pembayaran Otomatis -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 25px; border-radius: 14px; height: fit-content;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; margin-bottom: 20px;">
            <i class="fa-solid fa-cash-register me-2"></i> Kasir Pembayaran Keluar
        </h4>
        
        <form method="POST" action="">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Plat Nomor Kendaraan</label>
                <input type="text" name="plat_nomor" value="<?php echo htmlspecialchars(isset($found_data['plat_nomor']) ? $found_data['plat_nomor'] : ''); ?>" class="form-control" placeholder="Klik 'Proses Keluar' di tabel kanan" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" readonly required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Jenis Kendaraan</label>
                <input type="text" name="jenis_kendaraan" value="<?php echo htmlspecialchars(isset($found_data['jenis_kendaraan']) ? $found_data['jenis_kendaraan'] : '-'); ?>" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" readonly required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Durasi Parkir</label>
                <input type="number" name="durasi_jam" value="<?php echo $durasi_jam; ?>" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" readonly required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Total Biaya (Rp)</label>
                <input type="number" name="biaya" value="<?php echo $total_biaya; ?>" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" readonly required>
            </div>

            <!-- Pilihan Metode Pembayaran -->
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Metode Pembayaran</label>
                <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" onchange="toggleMetode()" required>
                    <option value="Tunai" style="background: #12141c;">Tunai (Cash)</option>
                    <option value="QRIS" style="background: #12141c;">QRIS (Digital / E-Wallet)</option>
                </select>
            </div>

            <!-- Input Uang Tunai (Tampil jika metode Tunai) -->
            <div style="margin-bottom: 20px;" id="wrapper-tunai">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Uang Tunai Diterima (Rp)</label>
                <input type="number" name="bayar" id="input_bayar" class="form-control" placeholder="Masukkan nominal uang" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;">
            </div>

            <!-- Tampilan QR Code (Tampil jika metode QRIS) -->
            <div style="margin-bottom: 20px; text-align: center; display: none;" id="wrapper-qris">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--gold-light); text-transform: uppercase; margin-bottom: 8px;">Scan QRIS SKE</label>
                <div style="background: #fff; padding: 10px; display: inline-block; border-radius: 8px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SKE-PARKING-<?php echo urlencode(isset($found_data['plat_nomor']) ? $found_data['plat_nomor'] : 'TEST'); ?>" alt="QRIS Code" style="width: 130px; height: 130px;">
                </div>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Scan menggunakan M-Banking atau E-Wallet (BCA, GoPay, OVO, dll)</p>
            </div>

            <button type="submit" name="proses_bayar" style="width: 100%; padding: 11px; background: linear-gradient(135deg, #d4af37, #aa8c2c); color: #0f1015; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; text-transform: uppercase; font-size: 12px; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
                Proses Pembayaran & Cetak Struk
            </button>
        </form>
    </div>

    <!-- Tabel Kendaraan Aktif di Area -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 25px; border-radius: 14px;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; margin-bottom: 20px;">
            <i class="fa-solid fa-list me-2"></i> Kendaraan Aktif di Area Parkir
        </h4>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-gold); color: var(--gold-light);">
                        <th style="padding: 10px; width: 20%;">Plat Nomor</th>
                        <th style="padding: 10px; width: 25%;">Jenis</th>
                        <th style="padding: 10px; width: 35%; white-space: nowrap;">Waktu Masuk</th>
                        <th style="padding: 10px; width: 20%; text-align: center; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql_aktif = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan WHERE status = 'Parkir' OR status = ''");
                    if ($sql_aktif && mysqli_num_rows($sql_aktif) > 0) {
                        while ($row = mysqli_fetch_assoc($sql_aktif)) {
                            $waktu = !empty($row['waktu_masuk']) ? $row['waktu_masuk'] : '-';
                            echo "<tr style='border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--text-muted);'>";
                            echo "<td style='padding: 12px; color: #fff; font-weight: 600;'>{$row['plat_nomor']}</td>";
                            echo "<td style='padding: 12px;'>{$row['jenis_kendaraan']}</td>";
                            echo "<td style='padding: 12px; white-space: nowrap;'>{$waktu}</td>";
                            echo "<td style='padding: 12px; text-align: center; white-space: nowrap;'>";
                            echo "<a href='index.php?page=pembayaran&aksi=keluar&plat=" . urlencode($row['plat_nomor']) . "' style='display: inline-block; padding: 6px 14px; background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #e74c3c; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 11px;'>Proses Keluar</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='padding: 20px; text-align: center; color: var(--text-muted);'>Tidak ada kendaraan aktif saat ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Script untuk Toggle Form Tunai / QRIS secara Dinamis -->
<script>
function toggleMetode() {
    var metode = document.getElementById('metode_pembayaran').value;
    var wrapperTunai = document.getElementById('wrapper-tunai');
    var wrapperQris = document.getElementById('wrapper-qris');
    var inputBayar = document.getElementById('input_bayar');

    if (metode === 'QRIS') {
        wrapperTunai.style.display = 'none';
        wrapperQris.style.display = 'block';
        inputBayar.removeAttribute('required');
    } else {
        wrapperTunai.style.display = 'block';
        wrapperQris.style.display = 'none';
        inputBayar.setAttribute('required', 'true');
    }
}
</script>