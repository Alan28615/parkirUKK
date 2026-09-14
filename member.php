<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Validasi ketat: Hanya Administrator yang boleh akses halaman ini
if (!isset($_SESSION['id_user']) || $_SESSION['level'] != 'Administrator') {
    echo "<div style='color: #e74c3c; padding: 20px;'>Akses ditolak! Halaman ini khusus untuk Administrator.</div>";
    exit();
}

include 'koneksi.php';

$pesan = '';
$tipe_pesan = '';

// Proses Tambah Member Baru
if (isset($_POST['tambah_member'])) {
    $nama_member     = mysqli_real_escape_string($koneksi, $_POST['nama_member']);
    $no_telepon      = mysqli_real_escape_string($koneksi, $_POST['no_telepon']);
    $plat_nomor      = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $jenis_kendaraan = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $masa_berlaku    = mysqli_real_escape_string($koneksi, $_POST['masa_berlaku']);

    $query = "INSERT INTO tb_member (nama_member, no_telepon, plat_nomor, jenis_kendaraan, masa_berlaku) 
              VALUES ('$nama_member', '$no_telepon', '$plat_nomor', '$jenis_kendaraan', '$masa_berlaku')";
    
    if (mysqli_query($koneksi, $query)) {
        $pesan = "Data member berhasil ditambahkan!";
        $tipe_pesan = "success";
    } else {
        $pesan = "Gagal menyimpan member: " . mysqli_error($koneksi);
        $tipe_pesan = "danger";
    }
}
?>

<div class="content-header" style="margin-bottom: 25px;">
    <h2 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 26px;">Manajemen Data Member Parkir</h2>
    <p style="color: var(--text-muted); font-size: 13px;">Kelola langganan kartu member atau akses khusus pengunjung SKE.</p>
</div>

<?php if (!empty($pesan)): ?>
    <div style="padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; background: <?= ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.15)' : 'rgba(231, 76, 60, 0.15)'; ?>; border: 1px solid <?= ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.4)' : 'rgba(231, 76, 60, 0.4)'; ?>; color: <?= ($tipe_pesan == 'success') ? '#2ecc71' : '#e74c3c'; ?>;">
        <?= $pesan; ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
    
    <!-- Form Tambah Member -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 25px; border-radius: 14px; height: fit-content;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; margin-bottom: 20px;">
            <i class="fa-solid fa-id-card me-2"></i> Tambah Member Baru
        </h4>
        
        <form method="POST" action="">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Nama Member</label>
                <input type="text" name="nama_member" class="form-control" placeholder="Nama lengkap member" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">No. Telepon / WhatsApp</label>
                <input type="text" name="no_telepon" class="form-control" placeholder="Contoh: 08123456789" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Plat Nomor Kendaraan</label>
                <input type="text" name="plat_nomor" class="form-control" placeholder="Contoh: AB 1234 AA" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Jenis Kendaraan</label>
                <select name="jenis_kendaraan" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
                    <option value="Motor">Sepeda Motor</option>
                    <option value="Mobil">Mobil / Minibus</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Masa Berlaku Berakhir</label>
                <input type="date" name="masa_berlaku" class="form-control" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
            </div>

            <button type="submit" name="tambah_member" style="width: 100%; padding: 11px; background: linear-gradient(135deg, #d4af37, #aa8c2c); color: #0f1015; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; text-transform: uppercase; font-size: 12px; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
                Simpan Member
            </button>
        </form>
    </div>

    <!-- Tabel Daftar Member -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 25px; border-radius: 14px;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; margin-bottom: 20px;">
            <i class="fa-solid fa-users-rectangle me-2"></i> Daftar Member Terdaftar
        </h4>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-gold); color: var(--gold-light);">
                        <th style="padding: 10px;">Nama</th>
                        <th style="padding: 10px;">Kontak</th>
                        <th style="padding: 10px;">Plat & Jenis</th>
                        <th style="padding: 10px;">Masa Berlaku</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tb_member'");
                    if (mysqli_num_rows($cek_tabel) > 0) {
                        $sql_member = mysqli_query($koneksi, "SELECT * FROM tb_member ORDER BY id_member DESC");
                        if (mysqli_num_rows($sql_member) > 0) {
                            while ($row = mysqli_fetch_assoc($sql_member)) {
                                echo "<tr style='border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--text-muted);'>";
                                echo "<td style='padding: 10px; color: #fff; font-weight: 600;'>{$row['nama_member']}</td>";
                                echo "<td style='padding: 10px;'>{$row['no_telepon']}</td>";
                                echo "<td style='padding: 10px;'>{$row['plat_nomor']} <br><small style='color:var(--gold-light);'>({$row['jenis_kendaraan']})</small></td>";
                                echo "<td style='padding: 10px; color: #2ecc71;'>{$row['masa_berlaku']}</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='padding: 15px; text-align: center; color: var(--text-muted);'>Belum ada data member.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='padding: 15px; text-align: center; color: #e74c3c;'>Tabel `tb_member` belum dibuat di database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>