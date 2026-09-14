<?php
session_start();
include 'koneksi.php';

$login_status = ''; 

// Proses jika form login disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Pengecekan khusus untuk akun Owner default
    if ($username === 'owner123' && $password === 'owner123') {
        $_SESSION['id_user'] = 999;
        $_SESSION['username'] = 'owner123';
        $_SESSION['level'] = 'Owner';
        
        $login_status = 'success';
    } else {
        // Pengecekan login standar dari database tabel tb_user
        $query = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");
        if ($query && mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            
            // Verifikasi password
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['level'] = $user['level'];
                
                $login_status = 'success';
            } else {
                $login_status = 'failed';
            }
        } else {
            $login_status = 'failed';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem - Sindu Kusuma Edupark</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-dark: #0f1015;
            --gold-primary: #d4af37;
            --gold-light: #f3e5ab;
            --gold-dark: #aa8c2c;
            --text-main: #f8f9fa;
            --text-muted: #adb5bd;
            --border-color: rgba(212, 175, 55, 0.3);
            --transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; position: relative; overflow-x: hidden; padding: 40px 20px; }

        .luxury-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(15, 16, 21, 0.8), rgba(15, 16, 21, 0.95)), 
                        url('https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?q=80&w=1920&auto=format&fit=crop') no-repeat center center/cover;
            z-index: -1; filter: brightness(0.6);
        }

        .auth-card {
            background: rgba(25, 27, 35, 0.85); border: 1px solid var(--border-color); border-radius: 20px;
            padding: 40px; width: 100%; max-width: 480px; backdrop-filter: blur(15px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7); text-align: center;
        }

        .auth-brand i { font-size: 36px; color: var(--gold-primary); margin-bottom: 15px; }
        .auth-brand h2 { font-family: 'Playfair Display', serif; font-size: 26px; color: #fff; margin-bottom: 8px; }
        .auth-brand p { font-size: 13px; color: var(--text-muted); margin-bottom: 30px; }

        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--gold-light); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap i { position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; }
        .form-control { width: 100%; padding: 12px 16px 12px 45px; background: rgba(15, 16, 21, 0.6); border: 1px solid var(--border-color); border-radius: 10px; color: #fff; font-size: 14px; transition: var(--transition); }
        .form-control:focus { outline: none; border-color: var(--gold-primary); box-shadow: 0 0 10px rgba(212, 175, 55, 0.2); }

        .btn-auth { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--gold-primary), var(--gold-dark)); color: #0f1015; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; transition: var(--transition); box-shadow: 0 5px 20px rgba(212, 175, 55, 0.3); margin-top: 10px; }
        .btn-auth:hover { background: linear-gradient(135deg, var(--gold-light), var(--gold-primary)); transform: translateY(-2px); }

        .auth-footer { margin-top: 25px; font-size: 13px; color: var(--text-muted); }
        .auth-footer a { color: var(--gold-light); text-decoration: none; font-weight: 600; transition: var(--transition); }
        .auth-footer a:hover { text-decoration: underline; }
        .back-home { display: inline-block; margin-top: 20px; font-size: 13px; color: var(--text-muted); text-decoration: none; transition: var(--transition); }
        .back-home:hover { color: var(--gold-light); }
    </style>
</head>
<body>

    <div class="luxury-bg"></div>

    <div class="auth-card">
        <div class="auth-brand">
            <i class="fa-solid fa-ferris-wheel"></i>
            <h2>Masuk Sistem</h2>
            <p>Sistem Parkir Sindu Kusuma Edupark</p>
        </div>

        <form action="" method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-at"></i>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div class="input-icon-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn-auth">Masuk Sekarang</button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="index.php?action=register">Daftar di sini</a>
        </div>
        
        <div>
            <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda Utama</a>
        </div>
    </div>

    <!-- SCRIPT AUDIO NOTIFIKASI -->
    <script>
        function playSound(type) {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                const now = audioCtx.currentTime;

                if (type === 'success') {
                    oscillator.type = 'sine';
                    oscillator.frequency.setValueAtTime(587.33, now); 
                    oscillator.frequency.setValueAtTime(880, now + 0.15); 
                    
                    gainNode.gain.setValueAtTime(0.15, now);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                    
                    oscillator.start(now);
                    oscillator.stop(now + 0.4);
                } else {
                    oscillator.type = 'sawtooth';
                    oscillator.frequency.setValueAtTime(180, now); 
                    oscillator.frequency.setValueAtTime(120, now + 0.15); 
                    
                    gainNode.gain.setValueAtTime(0.2, now);
                    gainNode.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                    
                    oscillator.start(now);
                    oscillator.stop(now + 0.4);
                }
            } catch (e) {
                console.log("AudioContext diblokir browser.");
            }
        }

        <?php if ($login_status === 'success'): ?>
            playSound('success');
            setTimeout(function() {
                window.location = 'index.php?page=dashboard';
            }, 500);
        <?php elseif ($login_status === 'failed'): ?>
            playSound('failed');
            setTimeout(function() {
                alert('Username atau Password salah!');
            }, 200);
        <?php endif; ?>
    </script>
</body>
</html>