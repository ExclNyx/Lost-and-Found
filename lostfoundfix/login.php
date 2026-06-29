<?php
session_start();
$error = '';

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Jika sudah login, langsung redirect
if (isset($_SESSION['userId'])) {
    if ($_SESSION['userRole'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = trim($_POST['password'] ?? '');

    if (empty($identifier) || empty($password)) {
        $error = 'Username/email dan password wajib diisi.';
    } else {
        // Coba koneksi DB
        $pdo = null;
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=lost_found_db;charset=utf8mb4", 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $error = 'Tidak bisa terhubung ke database. Pastikan XAMPP MySQL aktif dan database sudah diimport.';
        }

        if ($pdo) {
            try {
                // Cari user berdasarkan username ATAU email, lalu cocokkan password
                $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = :id OR email = :id) LIMIT 1");
                $stmt->execute([':id' => $identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && $user['password'] === $password) {
                    // Login berhasil
                    $_SESSION['userId']   = $user['id'];
                    $_SESSION['userRole'] = $user['role'];
                    $_SESSION['userName'] = $user['nama'];

                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit;
                } else {
                    $error = 'Username/email atau password salah. Silakan coba lagi.';
                }
            } catch (PDOException $e) {
                $error = 'Kesalahan database: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Lost & Found UMM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-12px) rotate(2deg); }
            66% { transform: translateY(-6px) rotate(-1deg); }
        }
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 8s ease-in-out infinite 1s; }
        .float-3 { animation: float 7s ease-in-out infinite 2s; }
        .input-field {
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.12);
            color: #fff;
            transition: all 0.25s ease;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.35); }
        .input-field:focus {
            background: rgba(255,255,255,0.1);
            border-color: rgba(99,179,237,0.7);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99,179,237,0.15);
        }
        .btn-login {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            transition: all 0.25s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.45);
        }
        .btn-login:active { transform: translateY(0); }
        .error-box {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.4);
        }
    </style>
</head>
<body class="min-h-screen bg-[#0f1624] flex items-center justify-center px-4 relative overflow-hidden">

    <!-- Background blobs -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="float-1 absolute -top-40 -left-40 w-96 h-96 rounded-full opacity-20" style="background: radial-gradient(circle, #3b82f6, transparent 70%);"></div>
        <div class="float-2 absolute -bottom-40 -right-40 w-96 h-96 rounded-full opacity-20" style="background: radial-gradient(circle, #6366f1, transparent 70%);"></div>
        <div class="float-3 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 rounded-full opacity-10" style="background: radial-gradient(circle, #06b6d4, transparent 70%);"></div>
    </div>

    <!-- Card -->
    <div class="glass rounded-3xl w-full max-w-md px-8 py-10 relative z-10 shadow-2xl">

        <!-- Logo & Heading -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg,#3b82f6,#6366f1);">
                <i data-lucide="search" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Lost & Found UMM</h1>
            <p class="text-sm text-blue-300 mt-1">Universitas Muhammadiyah Malang</p>
        </div>

        <!-- Error -->
        <?php if ($error): ?>
        <div class="error-box rounded-2xl px-4 py-3 mb-5 flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-400 shrink-0 mt-0.5"></i>
            <p class="text-sm text-red-300"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-blue-200 mb-1.5 uppercase tracking-wider">Username / Email</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-300 pointer-events-none"></i>
                    <input
                        type="text"
                        name="identifier"
                        required
                        placeholder="Masukkan username atau email"
                        value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>"
                        class="input-field w-full rounded-xl pl-10 pr-4 py-3 text-sm"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-blue-200 mb-1.5 uppercase tracking-wider">Password</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-300 pointer-events-none"></i>
                    <input
                        type="password"
                        id="password-field"
                        name="password"
                        required
                        placeholder="Masukkan password"
                        class="input-field w-full rounded-xl pl-10 pr-11 py-3 text-sm"
                    />
                    <button type="button" onclick="togglePassword()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-blue-300 hover:text-white transition">
                        <i data-lucide="eye" id="eye-icon" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login w-full rounded-xl py-3.5 text-white font-bold text-sm mt-2">
                Masuk Sekarang →
            </button>
        </form>

        <!-- Hint akun -->
        <div class="mt-6 pt-5 border-t border-white border-opacity-10">
            <p class="text-center text-xs text-blue-300 opacity-60 mb-2">Akun demo tersedia:</p>
            <div class="grid grid-cols-3 gap-2">
                <button onclick="fillLogin('indah','password123')" class="glass rounded-xl px-2 py-2 text-center hover:bg-white hover:bg-opacity-10 transition cursor-pointer">
                    <p class="text-[10px] text-blue-300 font-semibold">Mahasiswa</p>
                    <p class="text-[9px] text-white opacity-50 mt-0.5">indah</p>
                </button>
                <button onclick="fillLogin('petugas1','password123')" class="glass rounded-xl px-2 py-2 text-center hover:bg-white hover:bg-opacity-10 transition cursor-pointer">
                    <p class="text-[10px] text-blue-300 font-semibold">Petugas</p>
                    <p class="text-[9px] text-white opacity-50 mt-0.5">petugas1</p>
                </button>
                <button onclick="fillLogin('admin','password123')" class="glass rounded-xl px-2 py-2 text-center hover:bg-white hover:bg-opacity-10 transition cursor-pointer">
                    <p class="text-[10px] text-blue-300 font-semibold">Admin</p>
                    <p class="text-[9px] text-white opacity-50 mt-0.5">admin</p>
                </button>
            </div>
        </div>

    </div>

    <script>
        lucide.createIcons();

        function togglePassword() {
            const field = document.getElementById('password-field');
            const icon  = document.getElementById('eye-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                field.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        function fillLogin(user, pass) {
            document.querySelector('[name="identifier"]').value = user;
            document.querySelector('[name="password"]').value = pass;
        }
    </script>
</body>
</html>
