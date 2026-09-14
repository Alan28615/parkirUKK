<?php
// Pastikan koneksi dan session sudah aktif di index.php
// Proses tambah kendaraan jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['plat_nomor'])) {
    $plat_nomor = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $jenis_kendaraan = mysqli_real_escape_string($koneksi, $_POST['jenis_kendaraan']);
    $waktu_masuk = date('Y-m-d H:i:s');
    $status = 'Parkir';

    $q_insert = "INSERT INTO tb_kendaraan (plat_nomor, jenis_kendaraan, waktu_masuk, status) VALUES ('$plat_nomor', '$jenis_kendaraan', '$waktu_masuk', '$status')";
    if (mysqli_query($koneksi, $q_insert)) {
        echo "<script>alert('Kendaraan berhasil ditambahkan!'); window.location='index.php?page=kendaraan';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan kendaraan!');</script>";
    }
}

// Ambil data kendaraan dari database (diurutkan berdasarkan waktu masuk terbaru)
$result_kendaraan = mysqli_query($koneksi, "SELECT * FROM tb_kendaraan ORDER BY waktu_masuk DESC");
?>

<div class="kendaraan-container">
    <div class="page-header-title">
        <h2 style="font-family:'Playfair Display',serif; color:var(--gold-light); font-size:26px; margin-bottom:5px;">
            <i class="fa-solid fa-car-side me-2"></i> Manajemen Data Kendaraan
        </h2>
        <p style="color:var(--text-muted); font-size:13px; margin-bottom:25px;">
            Kelola pencatatan kendaraan masuk dan monitor daftar kendaraan aktif secara real-time.
        </p>
    </div>

    <!-- Layout Grid: Form di Kiri, Tabel di Kanan -->
    <div class="kendaraan-grid">
        
        <!-- Kolom Form Tambah Kendaraan -->
        <div class="card-form-wrapper">
            <div class="card-box">
                <div class="card-box-header">
                    <h3><i class="fa-solid fa-plus-circle" style="color:var(--gold-light);"></i> Tambah Kendaraan Masuk</h3>
                    <p>Input data kendaraan baru ke area parkir SKE</p>
                </div>
                
                <form action="index.php?page=kendaraan" method="POST">
                    <div class="form-group-custom">
                        <label>Plat Nomor Kendaraan</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-id-card"></i>
                            <input type="text" name="plat_nomor" class="input-custom" placeholder="Contoh: AB 9999 XX" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group-custom">
                        <label>Jenis Kendaraan</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-car"></i>
                            <select name="jenis_kendaraan" class="input-custom" required>
                                <option value="" disabled selected>-- Pilih Jenis Kendaraan --</option>
                                <option value="Mobil Pribadi">Mobil Pribadi</option>
                                <option value="Bus/Minibus Pariwisata">Bus/Minibus Pariwisata</option>
                                <option value="Sepeda Motor">Sepeda Motor</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-custom">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Kendaraan
                    </button>
                </form>
            </div>
        </div>

        <!-- Kolom Tabel Daftar Kendaraan Aktif -->
        <div class="card-table-wrapper">
            <div class="card-box">
                <div class="card-box-header">
                    <h3><i class="fa-solid fa-list-ul" style="color:var(--gold-light);"></i> Daftar Kendaraan Aktif</h3>
                    <p>Monitoring status kendaraan di dalam area parkir</p>
                </div>

                <div class="table-responsive">
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">No</th>
                                <th>Plat Nomor</th>
                                <th>Jenis Kendaraan</th>
                                <th>Waktu Masuk</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($result_kendaraan) > 0) {
                                while ($row = mysqli_fetch_assoc($result_kendaraan)) {
                                    $statusClass = (strtolower($row['status']) == 'selesai') ? 'badge-selesai' : 'badge-parkir';
                                    $statusText = !empty($row['status']) ? $row['status'] : 'Parkir';
                            ?>
                                <tr>
                                    <td style="text-align: center; color: var(--text-muted);"><?= $no++; ?></td>
                                    <td><strong style="color: #fff;"><?= htmlspecialchars($row['plat_nomor']); ?></strong></td>
                                    <td><?= htmlspecialchars($row['jenis_kendaraan']); ?></td>
                                    <td style="color: var(--text-muted); font-size: 12px;"><?= $row['waktu_masuk']; ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge-status <?= $statusClass; ?>"><?= $statusText; ?></span>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">Belum ada data kendaraan tercatat.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    /* Styling Khusus Halaman Kendaraan agar Rapi & Elegan */
    .kendaraan-grid {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 25px;
        align-items: start;
    }

    .card-box {
        background: var(--bg-card);
        border: 1px solid var(--border-gold);
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .card-box-header {
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        padding-bottom: 12px;
    }

    .card-box-header h3 {
        font-family: 'Playfair Display', serif;
        font-size: 16px;
        color: var(--gold-light);
        margin-bottom: 4px;
    }

    .card-box-header p {
        font-size: 12px;
        color: var(--text-muted);
    }

    .form-group-custom {
        margin-bottom: 18px;
    }

    .form-group-custom label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: var(--gold-light);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .input-wrap {
        position: relative;
    }

    .input-wrap i {
        position: absolute;
        top: 50%;
        left: 14px;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 13px;
    }

    .input-custom {
        width: 100%;
        padding: 11px 14px 11px 40px;
        background: rgba(15, 16, 21, 0.6);
        border: 1px solid var(--border-gold);
        border-radius: 10px;
        color: #fff;
        font-size: 13px;
        transition: var(--transition);
    }

    .input-custom:focus {
        outline: none;
        border-color: var(--gold-light);
        box-shadow: 0 0 10px rgba(212, 175, 55, 0.15);
    }

    .btn-submit-custom {
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, var(--gold-light), #aa8c2c);
        color: #0f1015;
        border: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        margin-top: 5px;
        box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
    }

    .btn-submit-custom:hover {
        opacity: 0.9;
        transform: translateY(-2px);
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .table-custom th {
        background: rgba(212, 175, 55, 0.08);
        color: var(--gold-light);
        padding: 12px 14px;
        font-weight: 600;
        border-bottom: 1px solid var(--border-gold);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }

    .table-custom td {
        padding: 12px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        color: var(--text-main);
    }

    .table-custom tbody tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .badge-status {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-parkir {
        background: rgba(241, 196, 15, 0.12);
        color: #f1c40f;
        border: 1px solid rgba(241, 196, 15, 0.3);
    }

    .badge-selesai {
        background: rgba(46, 204, 113, 0.12);
        color: #2ecc71;
        border: 1px solid rgba(46, 204, 113, 0.3);
    }

    @media(max-width: 1024px) {
        .kendaraan-grid {
            grid-template-columns: 1fr;
        }
    }
</style>