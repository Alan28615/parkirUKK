<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

$pesan = '';
$sukses = '';

if (isset($_POST['register'])) {
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $cek_user = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        $pesan = "Username sudah digunakan, silakan pilih yang lain!";
    } else {
        $query = "INSERT INTO tb_user (username, nama_lengkap, password, level) VALUES ('$username', '$nama_lengkap', '$password', 'Petugas')";
        if (mysqli_query($koneksi, $query)) {
            $sukses = "Registrasi berhasil! Silakan masuk.";
        } else {
            $pesan = "Gagal melakukan registrasi: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun - Sindu Kusuma Edupark (SKE)</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-main: #0f1015;
            --bg-card: rgba(25, 27, 35, 0.85);
            --gold-primary: #d4af37;
            --gold-light: #f3e5ab;
            --text-main: #f8f9fa;
            --text-muted: #adb5bd;
            --border-gold: rgba(212, 175, 55, 0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: radial-gradient(circle at center, rgba(212, 175, 55, 0.05) 0%, transparent 70%);
        }

        .register-card {
            background: var(--bg-card);
            border: 1px solid var(--border-gold);
            border-radius: 20px;
            padding: 40px;
            width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .register-icon {
            font-size: 36px;
            color: var(--gold-primary);
            margin-bottom: 15px;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: var(--gold-light);
            font-size: 24px;
            margin-bottom: 5px;
        }

        p.subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 25px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .input-box {
            position: relative;
        }

        .input-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 12px 12px 40px;
            background: rgba(15, 16, 21, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            font-size: 13px;
            transition: 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gold-primary);
            box-shadow: 0 0 8px rgba(212, 175, 55, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #d4af37, #aa8c2c);
            color: #0f1015;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 13px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            margin-top: 10px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            opacity: 0.9;
        }

        .btn-back {
            display: block;
            width: 100%;
            padding: 10px;
            background: transparent;
            color: var(--text-muted);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            margin-top: 10px;
            transition: 0.3s;
            text-align: center;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.4);
            color: #e74c3c;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.4);
            color: #2ecc71;
            padding: 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .footer-text {
            margin-top: 20px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .footer-text a {
            color: var(--gold-light);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="register-card">
        <div class="register-icon">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h2>Registrasi Akun</h2>
        <p class="subtitle">Daftarkan akun admin baru SKE</p>

        <?php if (!empty($pesan)): ?>
            <div class="alert-error"><?= $pesan; ?></div>
        <?php endif; ?>

        <?php if (!empty($sukses)): ?>
            <div class="alert-success"><?= $sukses; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <div class="input-box">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <div class="input-box">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap Anda" required>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-box">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn-submit">Daftar Sekarang</button>
            
            <!-- Tombol Kembali ke Halaman Login -->
            <a href="login.php" class="btn-back">
                <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Kembali ke Login
            </a>
        </form>

        <div class="footer-text">
            Sudah punya akun? <a href="login.php">Masuk di sini</a>
        </div>
    </div>

</body>
</html>