<?php
session_start();
include 'koneksi.php';

$page = isset($_GET['page']) ? $_GET['page'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// PROSES SIMPAN PENDAFTARAN JIKA FORM DI-SUBMIT (DENGAN NO. HP)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $no_hp    = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    
    $query = "INSERT INTO tb_user (username, no_hp, password, level) VALUES ('$username', '$no_hp', '$password', 'Pengunjung')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pendaftaran berhasil! Silakan masuk.'); window.location='login.php';</script>";
        exit;
    } else {
        echo "<script>alert('Pendaftaran gagal!');</script>";
    }
}

// HITUNG TOTAL KENDARAAN MASUK YANG STATUSNYA MASIH AKTIF/PARKIR
$query_total_kendaraan = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tb_kendaraan WHERE status = 'Parkir' OR status = ''");
$data_kendaraan = mysqli_fetch_assoc($query_total_kendaraan);
$total_kendaraan_masuk = $data_kendaraan['total'];

// AMBIL TOTAL PENDAPATAN KESELURUHAN BULAN INI
$bulan_ini = date('m');
$tahun_ini = date('Y');
$q_pendapatan_bln = mysqli_query($koneksi, "SELECT SUM(total_biaya) as total FROM tb_pembayaran WHERE MONTH(tanggal_transaksi) = '$bulan_ini' AND YEAR(tanggal_transaksi) = '$tahun_ini'");
$d_pendapatan_bln = mysqli_fetch_assoc($q_pendapatan_bln);
$total_pendapatan_bulan_ini = isset($d_pendapatan_bln['total']) ? $d_pendapatan_bln['total'] : 0;

// MENENTUKAN JUMLAH HARI DALAM BULAN INI UNTUK GRAFIK HARIAN
$jumlah_hari_bulan_ini = cal_days_in_month(CAL_GREGORIAN, (int)$bulan_ini, (int)$tahun_ini);

// 1. DATA GRAFIK KEUANGAN OWNER (HARIAN - BULAN BERJALAN)
$keuangan_per_hari = array_fill(1, $jumlah_hari_bulan_ini, 0);
$q_grafik_owner = mysqli_query($koneksi, "SELECT DAY(tanggal_transaksi) as hari, SUM(total_biaya) as total FROM tb_pembayaran WHERE MONTH(tanggal_transaksi) = '$bulan_ini' AND YEAR(tanggal_transaksi) = '$tahun_ini' GROUP BY DAY(tanggal_transaksi)");
while ($row_go = mysqli_fetch_assoc($q_grafik_owner)) {
    $keuangan_per_hari[(int)$row_go['hari']] = (int)$row_go['total'];
}
$arr_label_grafik_hari = range(1, $jumlah_hari_bulan_ini);
$arr_data_grafik_owner = array_values($keuangan_per_hari);

// 1.B. DATA GRAFIK JUMLAH KENDARAAN HARIAN (UNTUK LANDING PAGE / BULAN BERJALAN)
$kendaraan_per_hari = array_fill(1, $jumlah_hari_bulan_ini, 0);
$q_grafik_kendaraan = mysqli_query($koneksi, "SELECT DAY(waktu_masuk) as hari, COUNT(*) as total FROM tb_transaksi WHERE MONTH(waktu_masuk) = '$bulan_ini' AND YEAR(waktu_masuk) = '$tahun_ini' GROUP BY DAY(waktu_masuk)");
if ($q_grafik_kendaraan) {
    while ($row_gk = mysqli_fetch_assoc($q_grafik_kendaraan)) {
        $kendaraan_per_hari[(int)$row_gk['hari']] = (int)$row_gk['total'];
    }
}
$arr_data_grafik_kendaraan = array_values($kendaraan_per_hari);

// 2. DATA GRAFIK KEUANGAN PETUGAS (BERDASARKAN METODE PEMBAYARAN / KATEGORI BULAN INI)
$q_grafik_petugas = mysqli_query($koneksi, "SELECT metode_pembayaran, SUM(total_biaya) as total FROM tb_pembayaran WHERE MONTH(tanggal_transaksi) = '$bulan_ini' GROUP BY metode_pembayaran");
$label_metode = [];
$data_metode = [];
while ($row_gp = mysqli_fetch_assoc($q_grafik_petugas)) {
    $label_metode[] = $row_gp['metode_pembayaran'] ? $row_gp['metode_pembayaran'] : 'Tunai/Cash';
    $data_metode[] = (int)$row_gp['total'];
}
if (empty($label_metode)) {
    $label_metode = ['Tunai', 'QRIS', 'Transfer'];
    $data_metode = [0, 0, 0];
}

// JIKA SUDAH LOGIN DAN MEMINTA HALAMAN DASHBOARD/APLIKASI
if (isset($_SESSION['id_user']) && $page != ''):
    $userLevel = isset($_SESSION['level']) ? $_SESSION['level'] : 'Petugas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Parkir - Sindu Kusuma Edupark (SKE)</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-main: #0a0b0f;
            --bg-card: #13151c;
            --bg-card-hover: #1a1d26;
            --gold-light: #f3e5ab;
            --gold-primary: #d4af37;
            --gold-dark: #aa8c2c;
            --border-gold: rgba(212, 175, 55, 0.18);
            --text-main: #f8f9fa;
            --text-muted: #94a3b8;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-subtle: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-main); }
        ::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.2); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--gold-primary); }

        .sidebar { width: 280px; background: var(--bg-card); border-right: 1px solid var(--border-gold); display: flex; flex-direction: column; justify-content: space-between; position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 24px 18px; box-shadow: 5px 0 25px rgba(0,0,0,0.3); }
        .sidebar-brand { padding: 4px 10px 22px 10px; border-bottom: 1px solid var(--border-gold); display: flex; align-items: center; gap: 14px; }
        .sidebar-brand-icon { width: 42px; height: 42px; border-radius: 12px; background: rgba(212, 175, 55, 0.08); border: 1px solid var(--border-gold); display: flex; align-items: center; justify-content: center; }
        .sidebar-brand-icon img { width: 30px; height: 30px; object-fit: contain; }
        .sidebar-brand h2 { font-family: 'Playfair Display', serif; color: var(--gold-light); font-size: 18px; letter-spacing: 0.5px; }
        
        .sidebar-menu { list-style: none; padding: 20px 0; flex-grow: 1; overflow-y: auto; }
        .menu-section-title { font-size: 10px; text-transform: uppercase; color: var(--text-muted); letter-spacing: 1.5px; padding: 0 12px 10px 12px; margin-top: 18px; font-weight: 700; opacity: 0.7; }
        
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; color: var(--text-muted); text-decoration: none; border-radius: 12px; font-size: 13px; font-weight: 500; transition: var(--transition); border: 1px solid transparent; }
        .menu-left { display: flex; align-items: center; gap: 14px; }
        .sidebar-menu a i { font-size: 15px; width: 22px; text-align: center; color: var(--text-muted); transition: var(--transition); }
        
        .sidebar-menu a:hover { background: var(--bg-card-hover); color: var(--text-main); border-color: rgba(255,255,255,0.03); transform: translateX(3px); }
        .sidebar-menu a:hover i { color: var(--gold-light); }
        .sidebar-menu a.active { background: linear-gradient(135deg, rgba(212, 175, 55, 0.18), rgba(212, 175, 55, 0.05)); color: var(--gold-light); font-weight: 600; border: 1px solid var(--border-gold); box-shadow: 0 4px 20px rgba(212, 175, 55, 0.08); }
        .sidebar-menu a.active i { color: var(--gold-primary); }
        
        .badge-count { background: linear-gradient(135deg, #e74c3c, #c0392b); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; box-shadow: 0 2px 8px rgba(231, 76, 60, 0.4); }
        .sidebar-divider { height: 1px; background: var(--border-gold); margin: 18px 8px; opacity: 0.4; }

        .main-content { margin-left: 280px; flex-grow: 1; display: flex; flex-direction: column; background: var(--bg-main); min-height: 100vh; }
        
        .topbar { background: rgba(19, 21, 28, 0.85); border-bottom: 1px solid var(--border-gold); padding: 18px 36px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 99; backdrop-filter: blur(12px); box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .topbar-title { font-size: 13px; color: var(--text-muted); font-weight: 500; display: flex; align-items: center; gap: 8px; }
        .topbar-title::before { content: ''; display: inline-block; width: 6px; height: 6px; background: var(--gold-primary); border-radius: 50%; }
        .topbar-right { display: flex; align-items: center; gap: 24px; }
        .user-profile { font-size: 13px; color: var(--gold-primary); font-weight: 500; background: rgba(212, 175, 55, 0.06); padding: 8px 16px; border-radius: 30px; border: 1px solid var(--border-gold); }
        .user-profile span { color: #fff; font-weight: 600; margin-left: 4px; }
        
        .btn-logout-top { background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.25); color: #e74c3c; padding: 8px 16px; border-radius: 30px; font-size: 12px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: var(--transition); }
        .btn-logout-top:hover { background: #e74c3c; color: #fff; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); transform: translateY(-1px); }

        .content-body { padding: 36px; max-width: 1400px; width: 100%; margin: 0 auto; }
        .dashboard-card { background: var(--bg-card); border: 1px solid var(--border-gold); border-radius: 20px; padding: 26px; box-shadow: var(--shadow-subtle); transition: var(--transition); }
        .dashboard-card:hover { border-color: rgba(212, 175, 55, 0.35); }
        
        /* CSS Tambahan untuk Mempercantik Tampilan Grafik */
        .chart-container-luxury {
            position: relative;
            height: 340px;
            width: 100%;
            background: linear-gradient(145deg, rgba(19, 21, 28, 0.9), rgba(10, 11, 15, 0.95));
            border-radius: 16px;
            padding: 10px;
            border: 1px solid rgba(212, 175, 55, 0.1);
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
           <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <img src="logo.jpeg" alt="Logo SKE">
                </div>
                <h2>Parkir SKE</h2>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="index.php?page=dashboard" class="<?= ($page == 'dashboard') ? 'active' : ''; ?>">
                        <div class="menu-left"><i class="fa-solid fa-house"></i> Home</div>
                    </a>
                </li>
                
                <?php if ($userLevel == 'Owner'): ?>
                    <div class="menu-section-title">Executive View</div>
                    <li>
                        <a href="index.php?page=laporan" class="<?= ($page == 'laporan') ? 'active' : ''; ?>">
                            <div class="menu-left"><i class="fa-solid fa-chart-pie"></i> Laporan Keuangan</div>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=kinerja" class="<?= ($page == 'kinerja') ? 'active' : ''; ?>">
                            <div class="menu-left"><i class="fa-solid fa-user-shield"></i> Audit Petugas</div>
                        </a>
                    </li>

                <?php elseif ($userLevel == 'Pengunjung'): ?>
                    <div class="menu-section-title">Menu Pengunjung</div>
                    <li>
                        <a href="index.php?page=booking" class="<?= ($page == 'booking') ? 'active' : ''; ?>">
                            <div class="menu-left"><i class="fa-solid fa-bookmark"></i> Booking Parkir Saya</div>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=popular" class="<?= ($page == 'popular') ? 'active' : ''; ?>">
                            <div class="menu-left"><i class="fa-solid fa-star"></i> Area Parkir SKE</div>
                        </a>
                    </li>

                <?php else: ?>
                    <div class="menu-section-title">Menu Petugas</div>
                    <li>
                        <a href="index.php?page=kendaraan" class="<?= ($page == 'kendaraan') ? 'active' : ''; ?>">
                            <div class="menu-left"><i class="fa-solid fa-car-side"></i> Data Kendaraan</div>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=pembayaran" class="<?= ($page == 'pembayaran') ? 'active' : ''; ?>">
                            <div class="menu-left"><i class="fa-solid fa-cash-register"></i> Pembayaran Kasir</div>
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=booking" class="<?= ($page == 'booking') ? 'active' : ''; ?>">
                            <div class="menu-left"><i class="fa-solid fa-bookmark"></i> Data Booking</div>
                            <span class="badge-count">7</span>
                        </a>
                    </li>
                <?php endif; ?>

                <div class="sidebar-divider"></div>
                
                <div class="menu-section-title">Discovery</div>
                <li>
                    <a href="index.php?page=releases" class="<?= ($page == 'releases') ? 'active' : ''; ?>">
                        <div class="menu-left"><i class="fa-solid fa-layer-group"></i> Releases</div>
                    </a>
                </li>
                <li>
                    <a href="index.php?page=popular" class="<?= ($page == 'popular') ? 'active' : ''; ?>">
                        <div class="menu-left"><i class="fa-solid fa-star"></i> Popular Area</div>
                    </a>
                </li>

                <div class="menu-section-title">Dukungan</div>
                <li>
                    <a href="index.php?page=help" class="<?= ($page == 'help') ? 'active' : ''; ?>">
                        <div class="menu-left"><i class="fa-solid fa-circle-question"></i> Bantuan (Help)</div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-title">Sistem Informasi Perparkiran Sindu Kusuma Edupark</div>
            <div class="topbar-right">
                <div class="user-profile">
                    <?= htmlspecialchars($userLevel); ?>: 
                    <span><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'user_ske'; ?></span>
                </div>
                <a href="logout.php" class="btn-logout-top">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <div class="content-body">
            <?php
            switch ($page) {
                case 'dashboard':
                    ?>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                    <h2 style="font-family:'Playfair Display',serif; color:var(--gold-light); font-size:28px; margin-bottom:6px; font-weight:700;">
                        <?= ($userLevel == 'Owner') ? 'Dashboard Eksekutif Owner' : (($userLevel == 'Pengunjung') ? 'Dashboard Pengunjung SKE' : 'Dashboard Petugas Kasir'); ?>
                    </h2>
                    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:30px; line-height: 1.5;">
                        Selamat datang kembali, <b style="color:var(--text-main);"><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></b>. 
                        <?= ($userLevel == 'Pengunjung') ? 'Berikut informasi area dan layanan parkir eksklusif untuk Anda.' : 'Berikut ringkasan statistik keuangan harian dan transaksi kasir di kawasan SKE.'; ?>
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 35px;">
                        <?php if ($userLevel != 'Pengunjung'): ?>
                        <div class="dashboard-card" style="display: flex; align-items: center; gap: 20px;">
                            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(212, 175, 55, 0.12); border: 1px solid var(--border-gold); display: flex; align-items: center; justify-content: center; color: var(--gold-primary); font-size: 22px;">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.8px;">Total Pendapatan Bulan Ini</div>
                                <div style="font-size: 22px; font-weight: 700; color: #fff; margin-top: 6px; font-family: 'Playfair Display', serif;">Rp <?= number_format($total_pendapatan_bulan_ini, 0, ',', '.'); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="dashboard-card" style="display: flex; align-items: center; gap: 20px;">
                            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(46, 204, 113, 0.12); border: 1px solid rgba(46, 204, 113, 0.3); display: flex; align-items: center; justify-content: center; color: #2ecc71; font-size: 22px;">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.8px;">Sistem Operasional</div>
                                <div style="font-size: 22px; font-weight: 700; color: #2ecc71; margin-top: 6px; font-family: 'Playfair Display', serif;">Online</div>
                            </div>
                        </div>

                        <div class="dashboard-card" style="display: flex; align-items: center; gap: 20px;">
                            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(52, 152, 219, 0.12); border: 1px solid rgba(52, 152, 219, 0.3); display: flex; align-items: center; justify-content: center; color: #3498db; font-size: 22px;">
                                <i class="fa-solid fa-car"></i>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.8px;">Total Kendaraan Masuk</div>
                                <div style="font-size: 22px; font-weight: 700; color: #fff; margin-top: 6px; font-family: 'Playfair Display', serif;">
                                    <?= number_format($total_kendaraan_masuk, 0, ',', '.'); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($userLevel != 'Pengunjung'): ?>
                    <div class="dashboard-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px;">
                            <h3 style="font-family:'Playfair Display',serif; color: var(--gold-light); font-size: 18px; font-weight: 700;">
                                <i class="fa-solid fa-wallet" style="margin-right: 8px;"></i> 
                                <?= ($userLevel == 'Owner') ? 'Grafik Pendapatan Keuangan Harian (Owner)' : 'Grafik Pendapatan Berdasarkan Metode Pembayaran (Petugas)'; ?>
                            </h3>
                            <span style="font-size: 11.5px; color: var(--gold-light); background: rgba(212,175,55,0.08); padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border-gold); font-weight: 600;">
                                <?= ($userLevel == 'Owner') ? 'Statistik Harian Bulan Ini (' . date('F Y') . ')' : 'Statistik Bulan Ini'; ?>
                            </span>
                        </div>
                        <div class="chart-container-luxury">
                            <canvas id="grafikKeuanganDashboard"></canvas>
                        </div>
                    </div>

                    <script>
                        const ctxKeuangan = document.getElementById('grafikKeuanganDashboard').getContext('2d');
                        const userLevelType = "<?= $userLevel; ?>";

                        if (userLevelType === 'Owner') {
                            const gradientOwner = ctxKeuangan.createLinearGradient(0, 0, 0, 340);
                            gradientOwner.addColorStop(0, 'rgba(212, 175, 55, 0.55)');
                            gradientOwner.addColorStop(0.5, 'rgba(212, 175, 55, 0.15)');
                            gradientOwner.addColorStop(1, 'rgba(212, 175, 55, 0.0)');

                            const dataKeuanganOwner = <?= json_encode($arr_data_grafik_owner); ?>;
                            const labelHariOwner = <?= json_encode($arr_label_grafik_hari); ?>;

                            new Chart(ctxKeuangan, {
                                type: 'line',
                                data: {
                                    labels: labelHariOwner.map(d => 'Tgl ' + d),
                                    datasets: [{
                                        label: 'Pendapatan Harian (Rp)',
                                        data: dataKeuanganOwner,
                                        borderColor: '#d4af37',
                                        backgroundColor: gradientOwner,
                                        borderWidth: 3.5,
                                        fill: true,
                                        tension: 0.35,
                                        pointBackgroundColor: '#f3e5ab',
                                        pointBorderColor: '#13151c',
                                        pointBorderWidth: 2.5,
                                        pointRadius: 4,
                                        pointHoverRadius: 7,
                                        pointHoverBackgroundColor: '#ffffff',
                                        pointHoverBorderColor: '#d4af37'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    interaction: {
                                        mode: 'index',
                                        intersect: false,
                                    },
                                    plugins: {
                                        legend: { 
                                            position: 'top',
                                            labels: { color: '#f8f9fa', font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' }, boxWidth: 15 }
                                        },
                                        tooltip: {
                                            backgroundColor: 'rgba(19, 21, 28, 0.95)',
                                            titleColor: '#f3e5ab',
                                            bodyColor: '#f8f9fa',
                                            borderColor: 'rgba(212, 175, 55, 0.4)',
                                            borderWidth: 1.5,
                                            padding: 14,
                                            boxPadding: 6,
                                            callbacks: {
                                                title: function(context) {
                                                    return 'Hari / Tanggal: ' + context[0].label;
                                                },
                                                label: function(context) {
                                                    let value = context.parsed.y || 0;
                                                    return ' Pendapatan: Rp ' + value.toLocaleString('id-ID');
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { display: false },
                                            ticks: { color: '#94a3b8', font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }, maxTicksLimit: 15 }
                                        },
                                        y: {
                                            grid: { color: 'rgba(255, 255, 255, 0.04)', borderDash: [5, 5] },
                                            ticks: { 
                                                color: '#94a3b8', 
                                                font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, 
                                                beginAtZero: true,
                                                callback: function(value) {
                                                    if (value >= 1000000) {
                                                        return 'Rp ' + (value / 1000000).toFixed(1) + ' Juta';
                                                    } else if (value >= 1000) {
                                                        return 'Rp ' + (value / 1000) + 'rb';
                                                    }
                                                    return 'Rp ' + value;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        } else {
                            const labelMetode = <?= json_encode($label_metode); ?>;
                            const dataMetode = <?= json_encode($data_metode); ?>;

                            const gradQRIS = ctxKeuangan.createLinearGradient(0, 0, 0, 340);
                            gradQRIS.addColorStop(0, '#f3e5ab');
                            gradQRIS.addColorStop(1, '#d4af37');

                            const gradTunai = ctxKeuangan.createLinearGradient(0, 0, 0, 340);
                            gradTunai.addColorStop(0, '#58d68d');
                            gradTunai.addColorStop(1, '#27ae60');

                            const gradTransfer = ctxKeuangan.createLinearGradient(0, 0, 0, 340);
                            gradTransfer.addColorStop(0, '#5dade2');
                            gradTransfer.addColorStop(1, '#2980b9');

                            const gradLain = ctxKeuangan.createLinearGradient(0, 0, 0, 340);
                            gradLain.addColorStop(0, '#ec7063');
                            gradLain.addColorStop(1, '#c0392b');

                            new Chart(ctxKeuangan, {
                                type: 'bar',
                                data: {
                                    labels: labelMetode,
                                    datasets: [{
                                        label: 'Total Pendapatan (Rp)',
                                        data: dataMetode,
                                        backgroundColor: [gradQRIS, gradTunai, gradTransfer, gradLain],
                                        borderColor: ['#d4af37', '#2ecc71', '#3498db', '#e74c3c'],
                                        borderWidth: 2,
                                        borderRadius: 14,
                                        borderSkipped: false,
                                        barThickness: 50,
                                        maxBarThickness: 70
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { 
                                            position: 'top',
                                            labels: { color: '#f8f9fa', font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' }, boxWidth: 15 }
                                        },
                                        tooltip: {
                                            backgroundColor: 'rgba(19, 21, 28, 0.95)',
                                            titleColor: '#f3e5ab',
                                            bodyColor: '#f8f9fa',
                                            borderColor: 'rgba(212, 175, 55, 0.4)',
                                            borderWidth: 1.5,
                                            padding: 14,
                                            boxPadding: 6,
                                            callbacks: {
                                                title: function(context) {
                                                    return 'Metode: ' + context[0].label;
                                                },
                                                label: function(context) {
                                                    let value = context.parsed.y || 0;
                                                    return ' Total Pendapatan: Rp ' + value.toLocaleString('id-ID');
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        x: {
                                            grid: { display: false },
                                            ticks: { color: '#f8f9fa', font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' } }
                                        },
                                        y: {
                                            grid: { color: 'rgba(255, 255, 255, 0.05)', borderDash: [4, 4] },
                                            ticks: { 
                                                color: '#94a3b8', 
                                                font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, 
                                                beginAtZero: true,
                                                callback: function(value) {
                                                    if (value >= 1000000) {
                                                        return 'Rp ' + (value / 1000000).toFixed(1) + ' Juta';
                                                    } else if (value >= 1000) {
                                                        return 'Rp ' + (value / 1000) + 'rb';
                                                    }
                                                    return 'Rp ' + value;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    </script>
                    <?php endif; ?>
                    <?php
                    break;
                case 'popular':
                    ?>
                    <h2 style="font-family:'Playfair Display',serif; color:var(--gold-light); font-size:28px; margin-bottom:8px; font-weight:700;"><i class="fa-solid fa-star" style="margin-right: 8px;"></i> Area Parkir Terpopuler</h2>
                    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:30px;">Daftar zona dan titik area parkir dengan tingkat okupansi serta kunjungan tertinggi di kawasan SKE.</p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                        <div class="dashboard-card">
                            <div style="color: var(--gold-light); font-weight: 700; font-size: 16px; margin-bottom: 10px; display:flex; align-items:center; gap:10px;"><i class="fa-solid fa-square-parking" style="color:var(--gold-primary);"></i> Zona A - Gerbang Utama Barat</div>
                            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px; line-height: 1.6;">Area terfavorit pengunjung wahana permainan utama dengan kapasitas kendaraan roda empat terbesar.</p>
                            <span style="background: rgba(46, 204, 113, 0.12); color: #2ecc71; padding: 6px 14px; border-radius: 20px; font-size: 11.5px; font-weight: 600; border: 1px solid rgba(46,204,113,0.3);">Tingkat Okupansi: 88%</span>
                        </div>

                        <div class="dashboard-card">
                            <div style="color: var(--gold-light); font-weight: 700; font-size: 16px; margin-bottom: 10px; display:flex; align-items:center; gap:10px;"><i class="fa-solid fa-square-parking" style="color:var(--gold-primary);"></i> Zona B - Area Food Park & Latto</div>
                            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px; line-height: 1.6;">Titik parkir strategis dekat area kuliner dan pusat hiburan malam Sindu Kusuma Edupark.</p>
                            <span style="background: rgba(241, 196, 15, 0.12); color: #f1c40f; padding: 6px 14px; border-radius: 20px; font-size: 11.5px; font-weight: 600; border: 1px solid rgba(241,196,15,0.3);">Tingkat Okupansi: 75%</span>
                        </div>
                    </div>
                    <?php
                    break;
                case 'releases':
                    ?>
                    <h2 style="font-family:'Playfair Display',serif; color:var(--gold-light); font-size:28px; margin-bottom:8px; font-weight:700;"><i class="fa-solid fa-layer-group" style="margin-right: 8px;"></i> Pembaruan Sistem (Releases)</h2>
                    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:30px;">Catatan versi dan pembaruan fitur terbaru pada sistem informasi manajemen parkir SKE.</p>
                    
                    <div class="dashboard-card" style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                            <h3 style="color: var(--gold-light); font-size: 16.5px; font-weight: 700;">Versi 2.7.0 — Grafik Statistik Harian & Peningkatan UI CSS</h3>
                            <span style="font-size: 12px; color: var(--gold-primary); background: rgba(212,175,55,0.08); padding: 4px 12px; border-radius: 20px; border: 1px solid var(--border-gold);">Agustus 2026</span>
                        </div>
                        <p style="color: var(--text-muted); font-size: 13.5px; line-height: 1.6;">Pembaruan menyeluruh pada visualisasi grafik dari skala bulanan menjadi skala harian secara detail pada bulan berjalan, disertai perbaikan desain CSS kontainer grafik agar tampil lebih elegan, modern, dan interaktif.</p>
                    </div>
                    <?php
                    break;
                case 'help':
                    ?>
                    <h2 style="font-family:'Playfair Display',serif; color:var(--gold-light); font-size:28px; margin-bottom:8px; font-weight:700;">
                        <i class="fa-solid fa-circle-question" style="margin-right: 8px;"></i> Pusat Bantuan & Panduan SKE
                    </h2>
                    <p style="color:var(--text-muted); font-size:13.5px; margin-bottom:30px;">
                        Temukan panduan penggunaan sistem informasi perparkiran Sindu Kusuma Edupark berdasarkan hak akses Anda.
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 30px;">
                        <div class="dashboard-card">
                            <div style="color: var(--gold-light); font-weight: 700; font-size: 16px; margin-bottom: 14px;">
                                <i class="fa-solid fa-book-open" style="margin-right: 8px; color:var(--gold-primary);"></i> Panduan Umum Pengguna
                            </div>
                            <p style="color: var(--text-muted); font-size: 13.5px; line-height: 1.7; margin-bottom: 15px;">
                                Sistem parkir SKE dirancang untuk mempermudah pencatatan kendaraan masuk, monitoring slot area, hingga audit keuangan secara transparan. Pastikan Anda selalu melakukan <b style="color:#fff;">Logout</b> setelah selesai menggunakan perangkat.
                            </p>
                        </div>

                        <div class="dashboard-card">
                            <div style="color: var(--gold-light); font-weight: 700; font-size: 16px; margin-bottom: 14px;">
                                <i class="fa-solid fa-question" style="margin-right: 8px; color:var(--gold-primary);"></i> FAQ (Pertanyaan Umum)
                            </div>
                            <ul style="color: var(--text-muted); font-size: 13.5px; line-height: 1.7; padding-left: 18px;">
                                <li style="margin-bottom: 10px;"><b style="color:#fff;">Bagaimana cara daftar akun?</b> Pilih menu "Daftar Pengunjung" dan isi form termasuk nomor HP Anda.</li>
                                <li style="margin-bottom: 10px;"><b style="color:#fff;">Kendala tiket/pembayaran?</b> Segera hubungi pengawas shift atau Administrator IT SKE.</li>
                                <li><b style="color:#fff;">Lupa password?</b> Silakan hubungi bagian manajemen data parkir untuk reset akun.</li>
                            </ul>
                        </div>
                    </div>
                    <?php
                    break;
                case 'laporan':
                    if ($userLevel == 'Owner') {
                        echo '<h2 style="font-family:\'Playfair Display\',serif; color:var(--gold-light); font-size:28px; margin-bottom:8px; font-weight:700;">Laporan Keuangan Eksekutif</h2>';
                        echo '<p style="color:var(--text-muted); font-size:13.5px;">Halaman unduh rekapitulasi data pendapatan dan pajak parkir SKE.</p>';
                    } else {
                        echo "<p style='color:var(--text-muted);'>Akses ditolak. Halaman ini khusus Owner.</p>";
                    }
                    break;
                case 'kinerja':
                    if ($userLevel == 'Owner') {
                        echo '<h2 style="font-family:\'Playfair Display\',serif; color:var(--gold-light); font-size:28px; margin-bottom:8px; font-weight:700;">Audit Kinerja Petugas</h2>';
                        echo '<p style="color:var(--text-muted); font-size:13.5px;">Monitor aktivitas shift dan transaksi kasir lapangan secara berkala.</p>';
                    } else {
                        echo "<p style='color:var(--text-muted);'>Akses ditolak. Halaman ini khusus Owner.</p>";
                    }
                    break;
                case 'kendaraan':
                    if ($userLevel != 'Pengunjung') {
                        if (file_exists('kendaraan.php')) include 'kendaraan.php'; else echo "<p style='color:var(--text-muted);'>Halaman Data Kendaraan sedang disiapkan.</p>";
                    } else {
                        echo "<p style='color:var(--text-muted);'>Akses ditolak.</p>";
                    }
                    break;
                case 'pembayaran':
                    if ($userLevel != 'Pengunjung') {
                        if (file_exists('pembayaran.php')) include 'pembayaran.php'; else echo "<p style='color:var(--text-muted);'>Halaman Pembayaran Kasir sedang disiapkan.</p>";
                    } else {
                        echo "<p style='color:var(--text-muted);'>Akses ditolak.</p>";
                    }
                    break;
                case 'booking':
                    if (file_exists('booking.php')) include 'booking.php'; else echo "<p style='color:var(--text-muted);'>Halaman Data Booking sedang disiapkan.</p>";
                    break;
                default:
                    echo "<p style='color:var(--text-muted);'>Halaman tidak ditemukan.</p>";
                    break;
            }
            ?>
        </div>
    </div>

</body>
</html>

<?php 
// JIKA MEMILIH TAMPILAN DAFTAR (REGISTER)
elseif ($action == 'register'): 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sindu Kusuma Edupark</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0a0b0f;
            --gold-primary: #d4af37;
            --gold-light: #f3e5ab;
            --gold-dark: #aa8c2c;
            --text-main: #f8f9fa;
            --text-muted: #94a3b8;
            --border-color: rgba(212, 175, 55, 0.25);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; position: relative; overflow-x: hidden; padding: 40px 20px; }

        .luxury-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(10, 11, 15, 0.82), rgba(10, 11, 15, 0.96)), 
                        url('https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?q=80&w=1920&auto=format&fit=crop') no-repeat center center/cover;
            z-index: -1; filter: brightness(0.55);
        }

        .auth-card {
            background: rgba(19, 21, 28, 0.85); border: 1px solid var(--border-color); border-radius: 24px;
            padding: 45px 40px; width: 100%; max-width: 480px; backdrop-filter: blur(20px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.75); text-align: center;
        }

        .auth-brand-icon { width: 64px; height: 64px; margin: 0 auto 18px auto; border-radius: 18px; background: rgba(212,175,55,0.06); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; }
        .auth-brand-icon img { width: 40px; height: 40px; object-fit: contain; }
        .auth-brand h2 { font-family: 'Playfair Display', serif; font-size: 26px; color: #fff; margin-bottom: 6px; font-weight: 700; }
        .auth-brand p { font-size: 13px; color: var(--text-muted); margin-bottom: 30px; }

        .form-group { text-align: left; margin-bottom: 22px; }
        .form-group label { display: block; font-size: 11px; font-weight: 700; color: var(--gold-light); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.8px; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap i { position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; transition: var(--transition); }
        .form-control { width: 100%; padding: 13px 16px 13px 46px; background: rgba(10, 11, 15, 0.7); border: 1px solid var(--border-color); border-radius: 12px; color: #fff; font-size: 14px; transition: var(--transition); }
        .form-control:focus { outline: none; border-color: var(--gold-primary); box-shadow: 0 0 12px rgba(212, 175, 55, 0.25); background: rgba(10, 11, 15, 0.9); }
        .input-icon-wrap:focus-within i { color: var(--gold-primary); }

        .btn-auth { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark)); color: #0a0b0f; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; transition: var(--transition); box-shadow: 0 6px 20px rgba(212, 175, 55, 0.3); margin-top: 8px; }
        .btn-auth:hover { background: linear-gradient(135deg, var(--gold-light), var(--gold-primary)); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4); }

        .auth-footer { margin-top: 24px; font-size: 13px; color: var(--text-muted); }
        .auth-footer a { color: var(--gold-light); text-decoration: none; font-weight: 600; transition: var(--transition); }
        .auth-footer a:hover { text-decoration: underline; color: var(--gold-primary); }
        .back-home { display: inline-flex; align-items: center; gap: 8px; margin-top: 24px; font-size: 13px; color: var(--text-muted); text-decoration: none; transition: var(--transition); }
        .back-home:hover { color: var(--gold-light); }
    </style>
</head>
<body>

    <div class="luxury-bg"></div>

    <div class="auth-card">
        <div class="auth-brand">
            <div class="auth-brand-icon">
                <img src="logo.jpeg" alt="Logo SKE">
            </div>
            <h2>Pendaftaran Akun</h2>
            <p>Daftar sebagai Pengunjung Parkir SKE</p>
        </div>

        <form action="index.php?action=register" method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-at"></i>
                    <input type="text" name="username" class="form-control" placeholder="Pilih username unik" required>
                </div>
            </div>

            <div class="form-group">
                <label>No. Handphone (WhatsApp)</label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-phone"></i>
                    <input type="tel" name="no_hp" class="form-control" placeholder="Contoh: 081234567890" required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-auth">Daftar Sebagai Pengunjung</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
        
        <div>
            <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda Utama</a>
        </div>
    </div>

</body>
</html>

<?php 
// JIKA BELUM LOGIN ATAU MEMBUKA HALAMAN UTAMA (LANDING PAGE)
else: 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sindu Kusuma Edupark - Luxury Parking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg-dark: #0a0b0f;
            --gold-primary: #d4af37;
            --gold-light: #f3e5ab;
            --gold-dark: #aa8c2c;
            --text-main: #f8f9fa;
            --text-muted: #94a3b8;
            --border-color: rgba(212, 175, 55, 0.25);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: rgba(212, 175, 55, 0.2); border-radius: 10px; }

        .luxury-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(10, 11, 15, 0.78), rgba(10, 11, 15, 0.94)), 
                        url('https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?q=80&w=1920&auto=format&fit=crop') no-repeat center center/cover;
            z-index: -1; filter: brightness(0.55);
        }

        header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 24px 80px; border-bottom: 1px solid var(--border-color);
            background: rgba(10, 11, 15, 0.7); backdrop-filter: blur(15px);
            position: sticky; top: 0; z-index: 100;
        }

        .brand { display: flex; align-items: center; gap: 14px; }
        .brand-icon { width: 44px; height: 44px; border-radius: 12px; background: rgba(212,175,55,0.06); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; }
        .brand-icon img { width: 30px; height: 30px; object-fit: contain; }
        .brand h1 { font-family: 'Playfair Display', serif; font-size: 19px; background: linear-gradient(135deg, var(--gold-light), var(--gold-primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 700; }

        .nav-buttons { display: flex; gap: 14px; align-items: center; }
        .btn-outline { padding: 10px 22px; border: 1px solid var(--border-color); color: var(--gold-light); border-radius: 30px; text-decoration: none; font-size: 13px; font-weight: 600; transition: var(--transition); background: rgba(255, 255, 255, 0.02); }
        .btn-outline:hover { background: rgba(212, 175, 55, 0.12); border-color: var(--gold-primary); transform: translateY(-1px); }
        
        .btn-solid { padding: 10px 24px; background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark)); color: #0a0b0f; border-radius: 30px; text-decoration: none; font-size: 13px; font-weight: 700; transition: var(--transition); box-shadow: 0 4px 15px rgba(212, 175, 55, 0.25); }
        .btn-solid:hover { background: linear-gradient(135deg, var(--gold-light), var(--gold-primary)); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(212, 175, 55, 0.35); }

        .hero { flex-grow: 1; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 50px; align-items: center; padding: 60px 80px; max-width: 1300px; margin: 0 auto; width: 100%; }
        .hero-text h2 { font-family: 'Playfair Display', serif; font-size: 44px; margin-bottom: 20px; line-height: 1.18; color: #fff; }
        .hero-text h2 span { background: linear-gradient(135deg, var(--gold-light), var(--gold-primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-text p { color: var(--text-muted); font-size: 15px; margin-bottom: 32px; line-height: 1.7; }

        .hero-image-container { position: relative; border-radius: 20px; overflow: hidden; border: 1px solid var(--border-color); box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7); background: rgba(19, 21, 28, 0.8); }
        .hero-image-container img { width: 100%; height: auto; display: block; transition: var(--transition); }
        .hero-image-container:hover img { transform: scale(1.03); }
        .image-caption { position: absolute; bottom: 0; left: 0; width: 100%; background: linear-gradient(transparent, rgba(10, 11, 15, 0.95)); padding: 22px; font-size: 13px; color: var(--gold-light); font-weight: 500; text-align: center; }

        .parking-anim-section { max-width: 1240px; margin: 0 auto 50px auto; padding: 0 80px; width: 100%; }
        .parking-anim-card { background: rgba(19, 21, 28, 0.85); border: 1px solid var(--border-color); border-radius: 20px; padding: 32px; backdrop-filter: blur(15px); box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .parking-anim-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 10px; }
        .parking-anim-header h3 { font-family: 'Playfair Display', serif; font-size: 20px; color: var(--gold-light); }
        .parking-anim-header p { font-size: 12.5px; color: var(--text-muted); margin-top: 4px; }
        .parking-anim-stage { position: relative; width: 100%; height: 320px; border-radius: 14px; overflow: hidden; background: #13151c; border: 1px solid var(--border-color); }
        .parking-anim-stage video { width: 100%; height: 100%; object-fit: cover; display: block; }

        .stats-section { max-width: 1240px; margin: 0 auto 50px auto; padding: 0 80px; width: 100%; }
        .stats-container { background: rgba(19, 21, 28, 0.85); border: 1px solid var(--border-color); border-radius: 20px; padding: 32px; backdrop-filter: blur(15px); box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .stats-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .stats-header h3 { font-family: 'Playfair Display', serif; font-size: 20px; color: var(--gold-light); }
        .stats-header p { font-size: 12.5px; color: var(--text-muted); }
        
        .chart-container-landing {
            position: relative;
            height: 320px;
            width: 100%;
            background: linear-gradient(145deg, rgba(19, 21, 28, 0.9), rgba(10, 11, 15, 0.95));
            border-radius: 14px;
            padding: 12px;
            border: 1px solid rgba(212, 175, 55, 0.12);
        }

        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px; width: 100%; max-width: 1240px; margin: 0 auto 50px auto; padding: 0 80px; }
        .feature-card { background: rgba(19, 21, 28, 0.75); border: 1px solid var(--border-color); border-radius: 18px; padding: 32px; text-align: left; backdrop-filter: blur(12px); transition: var(--transition); }
        .feature-card:hover { transform: translateY(-5px); border-color: var(--gold-primary); box-shadow: 0 15px 35px rgba(0,0,0,0.4); }
        .feature-card i { font-size: 28px; color: var(--gold-primary); margin-bottom: 16px; }
        .feature-card h3 { font-family: 'Playfair Display', serif; font-size: 18px; margin-bottom: 10px; color: var(--gold-light); }
        .feature-card p { font-size: 13.5px; color: var(--text-muted); line-height: 1.6; }

        .testimonials-section { max-width: 1240px; margin: 0 auto 60px auto; padding: 0 80px; width: 100%; }
        .section-title { font-family: 'Playfair Display', serif; font-size: 26px; color: var(--gold-light); margin-bottom: 30px; text-align: center; }
        .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
        .testimonial-card { background: rgba(19, 21, 28, 0.8); border: 1px solid var(--border-color); border-radius: 18px; padding: 28px; backdrop-filter: blur(12px); transition: var(--transition); }
        .testimonial-card:hover { border-color: var(--gold-primary); transform: translateY(-3px); }
        .rating-stars { color: #f39c12; margin-bottom: 14px; font-size: 13.5px; }
        .testimonial-text { font-size: 13.5px; color: var(--text-muted); line-height: 1.7; margin-bottom: 18px; font-style: italic; }
        .testimonial-author { font-weight: 700; font-size: 14px; color: #fff; }
        .testimonial-role { font-size: 11.5px; color: var(--gold-primary); margin-top: 2px; }

        footer { text-align: center; padding: 28px; border-top: 1px solid var(--border-color); color: var(--text-muted); font-size: 12.5px; background: rgba(10, 11, 15, 0.9); }

        @media(max-width: 968px) {
            header { padding: 20px; }
            .hero { grid-template-columns: 1fr; padding: 40px 20px; text-align: center; }
            .hero-text h2 { font-size: 32px; }
            .hero-buttons { justify-content: center; }
            .features, .stats-section, .testimonials-section, .parking-anim-section { padding: 0 20px; }
        }
    </style>
</head>
<body>

    <div class="luxury-bg"></div>

    <header>
        <div class="brand">
            <div class="brand-icon">
                <img src="logo.jpeg" alt="Logo SKE">
            </div>
            <h1>Sindu Kusuma Edupark</h1>
        </div>
        <div class="nav-buttons">
            <a href="index.php?page=help" class="btn-outline"><i class="fa-solid fa-circle-question" style="margin-right: 6px;"></i> Bantuan</a>
            <a href="login.php" class="btn-outline">Masuk</a>
            <a href="index.php?action=register" class="btn-solid">Daftar Pengunjung</a>
        </div>
    </header>

    <div class="hero">
        <div class="hero-text">
            <h2>Sistem Manajemen Parkir <span>Eksklusif & Terpadu</span></h2>
            <p>Solusi digital mutakhir untuk pengelolaan area parkir, pencatatan kendaraan masuk dan keluar, serta pelaporan keuangan harian real-time di kawasan wisata Sindu Kusuma Edupark.</p>
            <div class="hero-buttons" style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="login.php" class="btn-solid" style="padding: 14px 28px; font-size: 14px;">Mulai Masuk Sistem</a>
                <a href="index.php?action=register" class="btn-outline" style="padding: 14px 28px; font-size: 14px;">Daftar Akun Pengunjung</a>
            </div>
        </div>
        
        <div class="hero-image-container">
            <img src="image_bfa11c.png" alt="Area Parkir Sindu Kusuma Edupark">
            <div class="image-caption"><i class="fa-solid fa-location-dot" style="color:var(--gold-primary);"></i> Gerbang & Area Parkir Utama Sindu Kusuma Edupark</div>
        </div>
    </div>

    <div class="parking-anim-section">
        <div class="parking-anim-card">
            <div class="parking-anim-header">
                <div>
                    <h3><i class="fa-solid fa-square-parking" style="margin-right: 8px; color:var(--gold-primary);"></i> Animasi Proses Parkir Otomatis</h3>
                    <p>Ilustrasi alur kendaraan masuk hingga terparkir rapi di slot yang tersedia</p>
                </div>
            </div>
            <div class="parking-anim-stage">
                <video autoplay loop muted playsinline poster="image_bfa11c.png">
                    <source src="hero_parkir.mp4" type="video/mp4">
                </video>
            </div>
        </div>
    </div>

    <!-- BAGIAN GRAFIK KENDARAAN HARIAN -->
    <div class="stats-section">
        <div class="stats-container">
            <div class="stats-header">
                <div>
                    <h3><i class="fa-solid fa-car" style="color: var(--gold-primary); margin-right: 8px;"></i> Grafik Statistik Volume Harian Kendaraan Masuk SKE</h3>
                    <p>Statistik rekapitulasi jumlah unit kendaraan masuk per hari pada bulan ini (<?= date('F Y') ?>)</p>
                </div>
            </div>
            <div class="chart-container-landing">
                <canvas id="landingDashboardKendaraanChart"></canvas>
            </div>
        </div>
    </div>

    <div class="features">
        <div class="feature-card">
            <i class="fa-solid fa-ticket"></i>
            <h3>Input Tiket Instan</h3>
            <p>Pencatatan data kendaraan masuk secara cepat dengan sistem pengelolaan area dan tarif otomatis.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-car-side"></i>
            <h3>Database Kendaraan</h3>
            <p>Monitoring riwayat dan status kendaraan secara menyeluruh yang sedang aktif terparkir di area.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-wallet"></i>
            <h3>Laporan Keuangan</h3>
            <p>Rekapitulasi pendapatan harian lengkap dengan grafik visualisasi statistik keuangan harian yang akurat.</p>
        </div>
    </div>

    <div class="testimonials-section">
        <h3 class="section-title">Penilaian & Ulasan Pengunjung</h3>
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="rating-stars">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p class="testimonial-text">"Sistem parkirnya sangat rapi dan terorganisir dengan baik. Masuk area waterpark jadi cepat tanpa antrean panjang di gerbang."</p>
                <div class="testimonial-author">Dimas Prasetyo</div>
                <div class="testimonial-role">Pengunjung Wisata</div>
            </div>

            <div class="testimonial-card">
                <div class="rating-stars">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <p class="testimonial-text">"Dashboard petugas sangat intuitif dan mudah digunakan. Pencatatan plat nomor serta laporan keuangan harian tersaji real-time."</p>
                <div class="testimonial-author">Ego Pratama</div>
                <div class="testimonial-role">Petugas Kasir Parkir</div>
            </div>

            <div class="testimonial-card">
                <div class="rating-stars">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star-half-stroke"></i>
                </div>
                <p class="testimonial-text">"Fitur booking slot parkirnya keren banget! Datang ke SKE jadi lebih tenang karena slot kendaraan sudah pasti aman."</p>
                <div class="testimonial-author">Siti Rahmawati</div>
                <div class="testimonial-role">Pengunjung VIP</div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; Alanda juhda Smk n1 sanden 2026. All Rights Reserved.</p>
    </footer>

    <script>
        const ctxLandingKendaraan = document.getElementById('landingDashboardKendaraanChart').getContext('2d');
        
        const gradientLandingKendaraan = ctxLandingKendaraan.createLinearGradient(0, 0, 0, 320);
        gradientLandingKendaraan.addColorStop(0, 'rgba(212, 175, 55, 0.55)');
        gradientLandingKendaraan.addColorStop(0.5, 'rgba(212, 175, 55, 0.15)');
        gradientLandingKendaraan.addColorStop(1, 'rgba(212, 175, 55, 0.0)');

        const dataLandingKendaraan = <?= json_encode($arr_data_grafik_kendaraan); ?>;
        const labelHariLanding = <?= json_encode($arr_label_grafik_hari); ?>;

        new Chart(ctxLandingKendaraan, {
            type: 'line',
            data: {
                labels: labelHariLanding.map(d => 'Tgl ' + d),
                datasets: [{
                    label: 'Jumlah Kendaraan Harian (Unit)',
                    data: dataLandingKendaraan,
                    borderColor: '#d4af37',
                    backgroundColor: gradientLandingKendaraan,
                    borderWidth: 3.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#f3e5ab',
                    pointBorderColor: '#13151c',
                    pointBorderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: '#d4af37'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { 
                        position: 'top',
                        labels: { color: '#f8f9fa', font: { family: "'Plus Jakarta Sans', sans-serif", size: 12, weight: '600' }, boxWidth: 15 }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(19, 21, 28, 0.95)',
                        titleColor: '#f3e5ab',
                        bodyColor: '#f8f9fa',
                        borderColor: 'rgba(212, 175, 55, 0.4)',
                        borderWidth: 1.5,
                        padding: 14,
                        boxPadding: 6,
                        callbacks: {
                            title: function(context) {
                                return 'Hari / Tanggal: ' + context[0].label;
                            },
                            label: function(context) {
                                let value = context.parsed.y || 0;
                                return ' Total Kendaraan: ' + value.toLocaleString('id-ID') + ' Unit';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }, maxTicksLimit: 15 }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)', borderDash: [5, 5] },
                        ticks: { 
                            color: '#94a3b8', 
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, 
                            beginAtZero: true,
                            callback: function(value) {
                                return value + ' Unit';
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
<?php endif; ?>