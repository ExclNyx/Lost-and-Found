<?php
include 'includes/header.php';
require_once 'includes/db.php';

if ($userRole !== 'admin') {
    header("Location: index.php"); exit;
}

$view = $_GET['view'] ?? 'overview';
$msg = '';

// Handle delete user
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $delId = intval($_GET['delete_user']);
    if ($delId != $_SESSION['userId']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$delId]);
    }
    header("Location: admin_dashboard.php?view=users&msg=user_deleted"); exit;
}

// Handle delete found item
if (isset($_GET['delete_item']) && is_numeric($_GET['delete_item'])) {
    $pdo->prepare("DELETE FROM found_items WHERE id = ?")->execute([intval($_GET['delete_item'])]);
    header("Location: admin_dashboard.php?view=barang&msg=item_deleted"); exit;
}

// Handle add/edit user POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add_user') {
        $nama = trim($_POST['nama']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];
        $no_hp = trim($_POST['no_hp'] ?? '');
        
        try {
            $pdo->prepare("INSERT INTO users (nama, username, email, password, role, no_hp) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$nama, $username, $email, $password, $role, $no_hp]);
            header("Location: admin_dashboard.php?view=users&msg=user_added"); exit;
        } catch(PDOException $e) {
            $error = "Gagal menambah user: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'add_item') {
        $namaBarang = trim($_POST['nama_barang']);
        $kategori = $_POST['kategori'];
        $deskSingkat = trim($_POST['deskripsi_singkat']);
        $deskLengkap = trim($_POST['deskripsi_lengkap']);
        $lokasi = trim($_POST['lokasi']);
        $waktu = trim($_POST['waktu_ditemukan']);
        
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir('./uploads')) mkdir('./uploads', 0755, true);
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $fn = 'found_admin_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], './uploads/' . $fn);
            $fotoPath = './uploads/' . $fn;
        }
        
        try {
            $pdo->prepare("INSERT INTO found_items (nama_barang, kategori, deskripsi_singkat, deskripsi_lengkap, lokasi, waktu_ditemukan, status, foto) VALUES (?, ?, ?, ?, ?, ?, 'available', ?)")
                ->execute([$namaBarang, $kategori, $deskSingkat, $deskLengkap, $lokasi, $waktu, $fotoPath]);
            header("Location: admin_dashboard.php?view=barang&msg=item_added"); exit;
        } catch(PDOException $e) {
            $error = "Gagal menambah barang: " . $e->getMessage();
        }
    }
}

// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalFound = $pdo->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
$totalTaken = $pdo->query("SELECT COUNT(*) FROM pickups")->fetchColumn();
$totalLaporan = $pdo->query("SELECT COUNT(*) FROM lost_reports")->fetchColumn();

// Messages
$msgMap = [
    'user_deleted' => '✅ Pengguna berhasil dihapus.',
    'user_added' => '✅ Pengguna baru berhasil ditambahkan.',
    'item_deleted' => '✅ Barang berhasil dihapus.',
    'item_added' => '✅ Barang temuan berhasil ditambahkan.',
];
if (isset($_GET['msg'])) $msg = $msgMap[$_GET['msg']] ?? '';

// Load data based on view
$users = $foundItems = $lostReports = $pickups = [];
if ($view === 'users') {
    $users = $pdo->query("SELECT * FROM users ORDER BY role, nama")->fetchAll(PDO::FETCH_ASSOC);
}
if ($view === 'barang') {
    $foundItems = $pdo->query("SELECT * FROM found_items ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
if ($view === 'reports') {
    $lostReports = $pdo->query("SELECT lr.*, u.nama as reporter FROM lost_reports lr JOIN users u ON lr.user_id = u.id ORDER BY lr.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $pickups = $pdo->query("SELECT p.*, f.nama_barang FROM pickups p JOIN found_items f ON p.found_item_id = f.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
if ($view === 'activity') {
    $lostReports = $pdo->query("SELECT lr.*, u.nama as reporter FROM lost_reports lr JOIN users u ON lr.user_id = u.id ORDER BY lr.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    $pickups = $pdo->query("SELECT p.*, f.nama_barang FROM pickups p JOIN found_items f ON p.found_item_id = f.id ORDER BY p.id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="flex h-screen overflow-hidden bg-[#1A202C]">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col md:ml-64 relative min-h-screen overflow-y-auto">
        <div class="pt-6 px-4 pb-4 w-full max-w-7xl mx-auto relative z-10 md:px-8 flex justify-between items-center">
            <h1 class="text-xl md:text-3xl font-semibold text-white">
                Dashboard <span class="font-bold text-blue-300">Super Admin</span>
            </h1>
            <a href="login.php?action=logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition">
                <i data-lucide="log-out" class="w-4 h-4"></i> Logout
            </a>
        </div>
        
        <div class="flex-1 relative z-20 px-0 md:px-8 pb-8">
            <div class="bg-[#F3F4F6] flex-1 rounded-t-3xl md:rounded-3xl w-full max-w-7xl mx-auto md:mx-0 px-5 md:px-8 pt-6 md:pt-8 pb-8 min-h-[70vh] shadow-2xl">

                <?php if ($msg): ?>
                <div class="mb-6 bg-green-100 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold border border-green-200"><?= $msg ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                <div class="mb-6 bg-red-100 text-red-700 px-4 py-3 rounded-xl text-sm font-semibold border border-red-200"><?= $error ?></div>
                <?php endif; ?>

                <!-- Tabs -->
                <div class="flex flex-wrap gap-2 mb-8 bg-white p-2 rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                    <a href="admin_dashboard.php?view=overview" class="px-5 py-2.5 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='overview' ? 'bg-[#1A202C] text-white' : 'text-gray-600 hover:bg-gray-100' ?>">📊 Overview</a>
                    <a href="admin_dashboard.php?view=users" class="px-5 py-2.5 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='users' ? 'bg-[#1A202C] text-white' : 'text-gray-600 hover:bg-gray-100' ?>">👥 Kelola Akun</a>
                    <a href="admin_dashboard.php?view=barang" class="px-5 py-2.5 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='barang' ? 'bg-[#1A202C] text-white' : 'text-gray-600 hover:bg-gray-100' ?>">📦 Kelola Barang</a>
                    <a href="admin_dashboard.php?view=reports" class="px-5 py-2.5 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='reports' ? 'bg-[#1A202C] text-white' : 'text-gray-600 hover:bg-gray-100' ?>">📋 View Laporan</a>
                    <a href="admin_dashboard.php?view=activity" class="px-5 py-2.5 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='activity' ? 'bg-[#1A202C] text-white' : 'text-gray-600 hover:bg-gray-100' ?>">📡 Monitoring</a>
                </div>

                <?php if ($view === 'overview'): ?>
                <!-- ===== OVERVIEW ===== -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <?php 
                    $stats = [
                        ['Total Pengguna', $totalUsers, 'users', 'bg-blue-100 text-blue-700'],
                        ['Barang Temuan', $totalFound, 'package', 'bg-green-100 text-green-700'],
                        ['Sudah Diambil', $totalTaken, 'package-check', 'bg-purple-100 text-purple-700'],
                        ['Laporan Hilang', $totalLaporan, 'file-text', 'bg-yellow-100 text-yellow-700'],
                    ];
                    foreach ($stats as $s): ?>
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full <?= $s[3] ?> flex items-center justify-center">
                                <i data-lucide="<?= $s[2] ?>" class="w-5 h-5"></i>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-slate-800"><?= $s[1] ?></p>
                        <p class="text-sm text-gray-500 mt-1"><?= $s[0] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="admin_dashboard.php?view=users" class="bg-slate-800 text-white p-6 rounded-2xl hover:bg-slate-900 transition flex items-center gap-4">
                        <i data-lucide="user-plus" class="w-8 h-8 text-blue-300"></i>
                        <div><p class="font-bold text-lg">Kelola Akun</p><p class="text-xs text-slate-400">Tambah / Hapus pengguna</p></div>
                    </a>
                    <a href="admin_dashboard.php?view=barang" class="bg-green-700 text-white p-6 rounded-2xl hover:bg-green-800 transition flex items-center gap-4">
                        <i data-lucide="package-plus" class="w-8 h-8 text-green-200"></i>
                        <div><p class="font-bold text-lg">Kelola Barang</p><p class="text-xs text-green-200">Tambah / Edit / Hapus barang</p></div>
                    </a>
                    <a href="admin_dashboard.php?view=reports" class="bg-blue-700 text-white p-6 rounded-2xl hover:bg-blue-800 transition flex items-center gap-4">
                        <i data-lucide="file-bar-chart" class="w-8 h-8 text-blue-200"></i>
                        <div><p class="font-bold text-lg">View Laporan</p><p class="text-xs text-blue-200">Semua laporan kehilangan & pengambilan</p></div>
                    </a>
                </div>

                <?php elseif ($view === 'users'): ?>
                <!-- ===== KELOLA AKUN (CRUD) ===== -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Tabel Users -->
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Daftar Pengguna</h3>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-4 font-semibold text-gray-600">Nama</th>
                                        <th class="p-4 font-semibold text-gray-600">Username</th>
                                        <th class="p-4 font-semibold text-gray-600">Role</th>
                                        <th class="p-4 font-semibold text-gray-600 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($users as $u): ?>
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="p-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs"><?= strtoupper(substr($u['nama'],0,1)) ?></div>
                                                <div>
                                                    <p class="font-medium text-slate-800"><?= htmlspecialchars($u['nama']) ?></p>
                                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($u['email']) ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-4 text-gray-600 text-sm"><?= htmlspecialchars($u['username']) ?></td>
                                        <td class="p-4">
                                            <span class="px-2 py-1 rounded-full text-xs font-bold <?= $u['role']==='admin' ? 'bg-red-100 text-red-700' : ($u['role']==='petugas' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') ?>">
                                                <?= ucfirst($u['role']) ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-center">
                                            <?php if($u['id'] != $_SESSION['userId']): ?>
                                            <a href="admin_dashboard.php?view=users&delete_user=<?= $u['id'] ?>" onclick="return confirm('Hapus pengguna <?= htmlspecialchars($u['nama']) ?>?')" class="text-red-500 hover:text-red-700 font-bold text-xs px-3 py-1 rounded-lg bg-red-50 hover:bg-red-100 transition">Hapus</a>
                                            <?php else: ?>
                                            <span class="text-xs text-gray-300">Anda</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Form Tambah User -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Tambah Pengguna</h3>
                        <form method="POST" action="admin_dashboard.php?view=users" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3" onsubmit="return validateUserForm(event)">
                            <input type="hidden" name="action" value="add_user">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="nama" required placeholder="Nama Lengkap" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Username <span class="text-red-500">*</span></label>
                                <input type="text" name="username" required placeholder="username unik" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="email@umm.ac.id" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
                                <input type="text" name="password" required placeholder="password" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">No HP</label>
                                <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Role <span class="text-red-500">*</span></label>
                                <select name="role" required class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200">
                                    <option value="mahasiswa">Mahasiswa</option>
                                    <option value="petugas">Petugas</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button type="submit" class="w-full bg-slate-800 text-white py-3 rounded-xl font-bold text-sm hover:bg-slate-900 transition">+ Tambah Pengguna</button>
                        </form>
                    </div>
                </div>

                <?php elseif ($view === 'barang'): ?>
                <!-- ===== KELOLA BARANG ===== -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Semua Barang Temuan</h3>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-4 font-semibold text-gray-600">Barang</th>
                                        <th class="p-4 font-semibold text-gray-600">Kat.</th>
                                        <th class="p-4 font-semibold text-gray-600">Status</th>
                                        <th class="p-4 font-semibold text-gray-600 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($foundItems as $fi): ?>
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="p-4">
                                            <p class="font-medium text-slate-800"><?= htmlspecialchars($fi['nama_barang']) ?></p>
                                            <p class="text-xs text-gray-400"><?= htmlspecialchars($fi['lokasi']) ?></p>
                                        </td>
                                        <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold <?= $fi['kategori']==='valuable' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' ?>"><?= ucfirst($fi['kategori']) ?></span></td>
                                        <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold <?= $fi['status']==='available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($fi['status']) ?></span></td>
                                        <td class="p-4 text-center">
                                            <a href="admin_dashboard.php?view=barang&delete_item=<?= $fi['id'] ?>" onclick="return confirm('Hapus barang ini?')" class="text-red-500 font-bold text-xs px-3 py-1 rounded bg-red-50 hover:bg-red-100 transition">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Form Tambah Barang -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Tambah Barang</h3>
                        <form method="POST" action="admin_dashboard.php?view=barang" enctype="multipart/form-data" class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                            <input type="hidden" name="action" value="add_item">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_barang" required placeholder="Nama Barang" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kategori <span class="text-red-500">*</span></label>
                                <select name="kategori" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200">
                                    <option value="non-valuable">Non Valuable</option>
                                    <option value="valuable">Valuable</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Lokasi <span class="text-red-500">*</span></label>
                                <input type="text" name="lokasi" required placeholder="Lokasi Ditemukan" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Waktu Ditemukan <span class="text-red-500">*</span></label>
                                <input type="text" name="waktu_ditemukan" required placeholder="27 Juni 2026, 10:00" class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Deskripsi Singkat <span class="text-red-500">*</span></label>
                                <input type="text" name="deskripsi_singkat" required placeholder="Ringkasan singkat..." class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Deskripsi Lengkap <span class="text-red-500">*</span></label>
                                <textarea name="deskripsi_lengkap" required rows="2" placeholder="Detail lengkap barang..." class="w-full bg-gray-50 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Foto Barang</label>
                                <input type="file" name="foto" accept="image/*" class="w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </div>
                            <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-green-700 transition">+ Tambah Barang</button>
                        </form>
                    </div>
                </div>

                <?php elseif ($view === 'reports'): ?>
                <!-- ===== VIEW LAPORAN ===== -->
                <div class="space-y-8">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Laporan Kehilangan</h3>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-4 font-semibold text-gray-600">Pelapor</th>
                                        <th class="p-4 font-semibold text-gray-600">Barang Hilang</th>
                                        <th class="p-4 font-semibold text-gray-600">Lokasi</th>
                                        <th class="p-4 font-semibold text-gray-600">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($lostReports)): ?>
                                    <tr><td colspan="4" class="p-8 text-center text-gray-400">Belum ada laporan.</td></tr>
                                    <?php else: ?>
                                    <?php foreach($lostReports as $r): ?>
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="p-4 font-medium text-slate-800"><?= htmlspecialchars($r['reporter']) ?><br/><span class="text-xs text-gray-400"><?= htmlspecialchars($r['kontak']) ?></span></td>
                                        <td class="p-4 text-gray-700"><?= htmlspecialchars($r['nama_barang']) ?></td>
                                        <td class="p-4 text-gray-600 text-xs"><?= htmlspecialchars($r['lokasi_hilang']) ?></td>
                                        <td class="p-4"><span class="px-2 py-1 rounded-full text-xs font-bold <?= $r['status_verifikasi'] ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>"><?= $r['status_verifikasi'] ? 'Verified' : 'Pending' ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4">Laporan Pengambilan</h3>
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden overflow-x-auto">
                            <table class="w-full text-left text-sm border-collapse">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="p-4 font-semibold text-gray-600">Pengambil</th>
                                        <th class="p-4 font-semibold text-gray-600">Barang</th>
                                        <th class="p-4 font-semibold text-gray-600">Petugas</th>
                                        <th class="p-4 font-semibold text-gray-600">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($pickups)): ?>
                                    <tr><td colspan="4" class="p-8 text-center text-gray-400">Belum ada data pengambilan.</td></tr>
                                    <?php else: ?>
                                    <?php foreach($pickups as $p): ?>
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="p-4 font-medium text-slate-800"><?= htmlspecialchars($p['nama_pengambil']) ?><br/><span class="text-xs text-gray-400"><?= htmlspecialchars($p['nim']) ?> | <?= htmlspecialchars($p['fakultas']) ?></span></td>
                                        <td class="p-4 text-gray-700"><?= htmlspecialchars($p['nama_barang']) ?></td>
                                        <td class="p-4 text-gray-600"><?= htmlspecialchars($p['nama_petugas']) ?></td>
                                        <td class="p-4 text-gray-600 text-xs"><?= htmlspecialchars($p['waktu_pengambilan']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php elseif ($view === 'activity'): ?>
                <!-- ===== MONITORING ===== -->
                <h3 class="text-lg font-bold text-slate-800 mb-6">Monitoring Aktivitas Sistem</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-yellow-500"></i> Laporan Kehilangan Terbaru</h4>
                        <ul class="space-y-3">
                            <?php foreach($lostReports as $r): ?>
                            <li class="flex items-start gap-3 border-b pb-3 last:border-0">
                                <div class="w-2 h-2 rounded-full mt-1.5 <?= $r['status_verifikasi'] ? 'bg-green-500' : 'bg-yellow-400' ?> shrink-0"></div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800"><?= htmlspecialchars($r['reporter']) ?> melaporkan kehilangan <strong><?= htmlspecialchars($r['nama_barang']) ?></strong></p>
                                    <p class="text-xs text-gray-400">Lokasi: <?= htmlspecialchars($r['lokasi_hilang']) ?></p>
                                </div>
                            </li>
                            <?php endforeach; ?>
                            <?php if(empty($lostReports)): ?><li class="text-sm text-gray-400">Belum ada aktivitas.</li><?php endif; ?>
                        </ul>
                    </div>
                    
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2"><i data-lucide="package-check" class="w-4 h-4 text-green-500"></i> Pengambilan Barang Terbaru</h4>
                        <ul class="space-y-3">
                            <?php foreach($pickups as $p): ?>
                            <li class="flex items-start gap-3 border-b pb-3 last:border-0">
                                <div class="w-2 h-2 rounded-full mt-1.5 bg-green-500 shrink-0"></div>
                                <div>
                                    <p class="text-sm font-medium text-slate-800"><strong><?= htmlspecialchars($p['nama_pengambil']) ?></strong> mengambil <strong><?= htmlspecialchars($p['nama_barang']) ?></strong></p>
                                    <p class="text-xs text-gray-400">Petugas: <?= htmlspecialchars($p['nama_petugas']) ?> — <?= htmlspecialchars($p['waktu_pengambilan']) ?></p>
                                </div>
                            </li>
                            <?php endforeach; ?>
                            <?php if(empty($pickups)): ?><li class="text-sm text-gray-400">Belum ada aktivitas.</li><?php endif; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
function validateUserForm(e) {
    const req = ['nama','username','email','password'];
    for (const r of req) {
        const el = document.querySelector(`[name="${r}"]`);
        if (!el || !el.value.trim()) { e.preventDefault(); alert(`Kolom wajib kosong!`); return false; }
    }
    return true;
}
lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
