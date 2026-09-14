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

// Proses Tambah Petugas Baru
if (isset($_POST['tambah_petugas'])) {
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi password
    $level        = 'Petugas'; // Default level untuk input dari menu ini

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan = "Username sudah digunakan, silakan pilih yang lain!";
        $tipe_pesan = "danger";
    } else {
        $query = "INSERT INTO tb_user (username, nama_lengkap, password, level) 
                  VALUES ('$username', '$nama_lengkap', '$password', '$level')";
        
        if (mysqli_query($koneksi, $query)) {
            $pesan = "Akun petugas berhasil ditambahkan!";
            $tipe_pesan = "success";
        } else {
            $pesan = "Gagal menyimpan petugas: " . mysqli_error($koneksi);
            $tipe_pesan = "danger";
        }
    }
}
?>

<div class="content-header" style="margin-bottom: 25px;">
    <h2 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 26px;">Manajemen Data Petugas</h2>
    <p style="color: var(--text-muted); font-size: 13px;">Kelola akun petugas operasional parkir SKE.</p>
</div>

<?php if (!empty($pesan)): ?>
    <div style="padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; background: <?= ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.15)' : 'rgba(231, 76, 60, 0.15)'; ?>; border: 1px solid <?= ($tipe_pesan == 'success') ? 'rgba(46, 204, 113, 0.4)' : 'rgba(231, 76, 60, 0.4)'; ?>; color: <?= ($tipe_pesan == 'success') ? '#2ecc71' : '#e74c3c'; ?>;">
        <?= $pesan; ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
    
    <!-- Form Tambah Petugas -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 25px; border-radius: 14px; height: fit-content;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; margin-bottom: 20px;">
            <i class="fa-solid fa-user-plus me-2"></i> Tambah Petugas Baru
        </h4>
        
        <form method="POST" action="">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username untuk login" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap petugas" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 5px;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password akun" style="width: 100%; padding: 10px; background: rgba(15, 16, 21, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 13px;" required>
            </div>

            <button type="submit" name="tambah_petugas" style="width: 100%; padding: 11px; background: linear-gradient(135deg, #d4af37, #aa8c2c); color: #0f1015; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; text-transform: uppercase; font-size: 12px; box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);">
                Simpan Petugas
            </button>
        </form>
    </div>

    <!-- Tabel Daftar Petugas -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-gold); padding: 25px; border-radius: 14px;">
        <h4 style="font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; margin-bottom: 20px;">
            <i class="fa-solid fa-user-shield me-2"></i> Daftar Akun Pengguna / Petugas
        </h4>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-gold); color: var(--gold-light);">
                        <th style="padding: 10px;">Username</th>
                        <th style="padding: 10px;">Nama Lengkap</th>
                        <th style="padding: 10px;">Level / Peran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tb_user'");
                    if (mysqli_num_rows($cek_tabel) > 0) {
                        $sql_user = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id_user DESC");
                        if (mysqli_num_rows($sql_user) > 0) {
                            while ($row = mysqli_fetch_assoc($sql_user)) {
                                $badge_color = ($row['level'] == 'Administrator') ? '#e74c3c' : (($row['level'] == 'Owner') ? '#f1c40f' : '#2ecc71');
                                echo "<tr style='border-bottom: 1px solid rgba(255,255,255,0.05); color: var(--text-muted);'>";
                                echo "<td style='padding: 10px; color: #fff; font-weight: 600;'>{$row['username']}</td>";
                                echo "<td style='padding: 10px;'>" . ($row['nama_lengkap'] ?? '-') . "</td>";
                                echo "<td style='padding: 10px;'><span style='background: {$badge_color}15; color: {$badge_color}; padding: 3px 8px; border-radius: 4px; font-size: 11px;'>{$row['level']}</span></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='padding: 15px; text-align: center; color: var(--text-muted);'>Belum ada data user.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='padding: 15px; text-align: center; color: #e74c3c;'>Tabel `tb_user` belum dibuat di database.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>