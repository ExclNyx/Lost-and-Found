<?php
include 'includes/header.php';
require_once 'includes/db.php';

if ($userRole !== 'petugas' && $userRole !== 'admin') {
    header("Location: index.php"); exit;
}

$view = $_GET['view'] ?? 'laporan_masuk';
$msg = '';

// Handle form tambah barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'tambah') {
        $namaBarang = trim($_POST['nama_barang'] ?? '');
        $kategori = $_POST['kategori'] ?? 'non-valuable';
        $deskSingkat = trim($_POST['deskripsi_singkat'] ?? '');
        $deskLengkap = trim($_POST['deskripsi_lengkap'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $waktu = trim($_POST['waktu_ditemukan'] ?? '');
        
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir('./uploads')) mkdir('./uploads', 0755, true);
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $fn = 'found_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], './uploads/' . $fn);
            $fotoPath = './uploads/' . $fn;
        }
        
        try {
            $pdo->prepare("INSERT INTO found_items (nama_barang, kategori, deskripsi_singkat, deskripsi_lengkap, lokasi, waktu_ditemukan, status, foto) VALUES (?, ?, ?, ?, ?, ?, 'available', ?)")
                ->execute([$namaBarang, $kategori, $deskSingkat, $deskLengkap, $lokasi, $waktu, $fotoPath]);
            header("Location: tambah_barang.php?view=laporan_masuk&msg=tambah_ok");
            exit;
        } catch(PDOException $e) {
            $error = $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $namaBarang = trim($_POST['nama_barang'] ?? '');
        $kategori = $_POST['kategori'] ?? 'non-valuable';
        $deskSingkat = trim($_POST['deskripsi_singkat'] ?? '');
        $deskLengkap = trim($_POST['deskripsi_lengkap'] ?? '');
        $lokasi = trim($_POST['lokasi'] ?? '');
        $waktu = trim($_POST['waktu_ditemukan'] ?? '');
        
        try {
            $pdo->prepare("UPDATE found_items SET nama_barang=?, kategori=?, deskripsi_singkat=?, deskripsi_lengkap=?, lokasi=?, waktu_ditemukan=? WHERE id=?")
                ->execute([$namaBarang, $kategori, $deskSingkat, $deskLengkap, $lokasi, $waktu, $id]);
            header("Location: tambah_barang.php?view=daftar_barang&msg=edit_ok");
            exit;
        } catch(PDOException $e) {
            $error = $e->getMessage();
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo->prepare("DELETE FROM found_items WHERE id = ?")->execute([intval($_GET['delete'])]);
        header("Location: tambah_barang.php?view=daftar_barang&msg=delete_ok");
        exit;
    } catch(PDOException $e) {}
}

if (isset($_GET['msg'])) {
    $msgs = [
        'tambah_ok' => '✅ Barang temuan berhasil ditambahkan!',
        'edit_ok' => '✅ Data barang berhasil diperbarui!',
        'delete_ok' => '✅ Barang berhasil dihapus!',
    ];
    $msg = $msgs[$_GET['msg']] ?? '';
}

// Edit mode
$editItem = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmtE = $pdo->prepare("SELECT * FROM found_items WHERE id = ?");
    $stmtE->execute([intval($_GET['edit'])]);
    $editItem = $stmtE->fetch(PDO::FETCH_ASSOC);
    $view = 'tambah';
}

// Fetch data for views
$lostReports = [];
$foundItems = [];
$pickups = [];

if ($view === 'laporan_masuk' || $view === 'daftar_barang' || $view === 'pengambilan') {
    try {
        $lostReports = $pdo->query("SELECT lr.*, u.nama as reporter, u.no_hp as hp FROM lost_reports lr JOIN users u ON lr.user_id = u.id ORDER BY lr.id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $foundItems = $pdo->query("SELECT * FROM found_items ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        $pickups = $pdo->query("SELECT p.*, f.nama_barang FROM pickups p JOIN found_items f ON p.found_item_id = f.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {}
}
?>

<div class="flex h-screen overflow-hidden bg-[#1A202C]">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col md:ml-64 relative min-h-screen overflow-y-auto">
        <?php include 'includes/top_header.php'; ?>
        
        <div class="flex-1 relative z-20 px-0 md:px-8 pb-24 md:pb-8">
            <div class="bg-[#F3F4F6] flex-1 rounded-t-3xl md:rounded-3xl w-full max-w-7xl mx-auto md:mx-0 relative z-20 px-5 md:px-8 pt-6 md:pt-8 pb-8 min-h-[70vh] shadow-2xl">

                <?php if ($msg): ?>
                <div class="mb-6 bg-green-100 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold border border-green-200"><?= $msg ?></div>
                <?php endif; ?>

                <!-- Sub Tabs -->
                <div class="flex flex-wrap gap-2 mb-6 bg-white p-2 rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                    <a href="tambah_barang.php?view=laporan_masuk" class="px-5 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='laporan_masuk' ? 'bg-slate-800 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">Laporan Kehilangan</a>
                    <a href="tambah_barang.php?view=pengambilan" class="px-5 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='pengambilan' ? 'bg-slate-800 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">Laporan Pengambilan</a>
                    <a href="tambah_barang.php?view=daftar_barang" class="px-5 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='daftar_barang' ? 'bg-slate-800 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">Data Barang Temuan</a>
                    <a href="tambah_barang.php?view=tambah" class="px-5 py-2 rounded-lg text-sm font-bold whitespace-nowrap transition <?= $view==='tambah' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' ?>">+ Tambah Barang</a>
                </div>

                <?php if ($view === 'laporan_masuk'): ?>
                <h3 class="text-lg font-bold text-slate-800 mb-4">Laporan Kehilangan Masuk</h3>
                <?php if (empty($lostReports)): ?>
                <div class="bg-white rounded-2xl p-8 text-center text-gray-400 border border-gray-100">Belum ada laporan kehilangan.</div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($lostReports as $r): ?>
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="font-bold text-slate-800"><?= htmlspecialchars($r['reporter']) ?></p>
                                <p class="text-xs text-gray-500">📞 <?= htmlspecialchars($r['kontak']) ?></p>
                            </div>
                            <span class="text-[11px] px-2 py-1 rounded-full font-bold <?= $r['status_verifikasi'] ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                                <?= $r['status_verifikasi'] ? 'Terverifikasi' : 'Menunggu' ?>
                            </span>
                        </div>
                        <p class="font-bold text-slate-700 mb-1">🔍 <?= htmlspecialchars($r['nama_barang']) ?></p>
                        <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($r['deskripsi']) ?></p>
                        <div class="flex flex-wrap gap-3 text-xs text-gray-500 border-t pt-3">
                            <span>📍 <?= htmlspecialchars($r['lokasi_hilang']) ?></span>
                            <span>🕐 <?= htmlspecialchars($r['waktu_hilang']) ?></span>
                            <div class="ml-auto">
                                <a href="laporan_action.php?action=<?= $r['status_verifikasi'] ? 'unverify' : 'verify' ?>&id=<?= $r['id'] ?>" 
                                   class="px-4 py-1.5 rounded-full text-[11px] font-bold text-white transition <?= $r['status_verifikasi'] ? 'bg-gray-400 hover:bg-gray-500' : 'bg-green-500 hover:bg-green-600' ?>">
                                    <?= $r['status_verifikasi'] ? 'Batal Verifikasi' : '✓ Verifikasi' ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php elseif ($view === 'pengambilan'): ?>
                <h3 class="text-lg font-bold text-slate-800 mb-4">Laporan Pengambilan Barang</h3>
                <a href="verify_pickup.php" class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold mb-4 hover:bg-green-700 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah Verifikasi Baru
                </a>
                <?php if (empty($pickups)): ?>
                <div class="bg-white rounded-2xl p-8 text-center text-gray-400 border border-gray-100">Belum ada data pengambilan.</div>
                <?php else: ?>
                <div class="overflow-x-auto bg-white rounded-2xl border border-gray-100 shadow-sm">
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
                            <?php foreach($pickups as $p): ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-4">
                                    <p class="font-medium text-slate-800"><?= htmlspecialchars($p['nama_pengambil']) ?></p>
                                    <p class="text-xs text-gray-400"><?= htmlspecialchars($p['nim']) ?> | <?= htmlspecialchars($p['fakultas']) ?></p>
                                </td>
                                <td class="p-4 text-gray-700"><?= htmlspecialchars($p['nama_barang']) ?></td>
                                <td class="p-4 text-gray-700"><?= htmlspecialchars($p['nama_petugas']) ?></td>
                                <td class="p-4 text-gray-600 text-xs"><?= htmlspecialchars($p['waktu_pengambilan']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php elseif ($view === 'daftar_barang'): ?>
                <h3 class="text-lg font-bold text-slate-800 mb-4">Data Barang Temuan</h3>
                <?php if (empty($foundItems)): ?>
                <div class="bg-white rounded-2xl p-8 text-center text-gray-400 border border-gray-100">Belum ada barang temuan.</div>
                <?php else: ?>
                <div class="overflow-x-auto bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-4 font-semibold text-gray-600">Barang</th>
                                <th class="p-4 font-semibold text-gray-600">Kategori</th>
                                <th class="p-4 font-semibold text-gray-600">Lokasi</th>
                                <th class="p-4 font-semibold text-gray-600">Status</th>
                                <th class="p-4 font-semibold text-gray-600 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($foundItems as $fi): ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-4 font-medium text-slate-800"><?= htmlspecialchars($fi['nama_barang']) ?></td>
                                <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold <?= $fi['kategori']==='valuable' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' ?>"><?= ucfirst($fi['kategori']) ?></span></td>
                                <td class="p-4 text-gray-600 text-xs"><?= htmlspecialchars($fi['lokasi']) ?></td>
                                <td class="p-4"><span class="px-2 py-1 rounded text-xs font-bold <?= $fi['status']==='available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>"><?= ucfirst($fi['status']) ?></span></td>
                                <td class="p-4 text-center flex gap-2 justify-center">
                                    <a href="tambah_barang.php?edit=<?= $fi['id'] ?>" class="text-blue-600 hover:text-blue-800 font-bold text-xs px-3 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 transition">Edit</a>
                                    <a href="tambah_barang.php?delete=<?= $fi['id'] ?>&view=daftar_barang" onclick="return confirm('Hapus barang ini?')" class="text-red-500 hover:text-red-700 font-bold text-xs px-3 py-1 rounded-lg bg-red-50 hover:bg-red-100 transition">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php elseif ($view === 'tambah'): ?>
                <h3 class="text-lg font-bold text-slate-800 mb-4"><?= $editItem ? 'Edit Barang Temuan' : 'Tambah Barang Temuan Baru' ?></h3>
                <div class="max-w-2xl">
                    <form method="POST" action="tambah_barang.php" enctype="multipart/form-data" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4" onsubmit="return validateBarangForm(event)">
                        <input type="hidden" name="action" value="<?= $editItem ? 'edit' : 'tambah' ?>">
                        <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Nama Barang <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_barang" required value="<?= htmlspecialchars($editItem['nama_barang'] ?? '') ?>" placeholder="Nama Barang" class="w-full bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Kategori <span class="text-red-500">*</span></label>
                                <select name="kategori" required class="w-full bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition">
                                    <option value="non-valuable" <?= ($editItem['kategori'] ?? '') === 'non-valuable' ? 'selected' : '' ?>>Non Valuable</option>
                                    <option value="valuable" <?= ($editItem['kategori'] ?? '') === 'valuable' ? 'selected' : '' ?>>Valuable</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Lokasi Ditemukan <span class="text-red-500">*</span></label>
                                <input type="text" name="lokasi" required value="<?= htmlspecialchars($editItem['lokasi'] ?? '') ?>" placeholder="Lokasi" class="w-full bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Waktu Ditemukan <span class="text-red-500">*</span></label>
                                <input type="text" name="waktu_ditemukan" required value="<?= htmlspecialchars($editItem['waktu_ditemukan'] ?? '') ?>" placeholder="contoh: 27 Juni 2026, 10:00" class="w-full bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Deskripsi Singkat <span class="text-red-500">*</span></label>
                            <input type="text" name="deskripsi_singkat" required value="<?= htmlspecialchars($editItem['deskripsi_singkat'] ?? '') ?>" placeholder="Satu kalimat ringkasan..." class="w-full bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Deskripsi Lengkap <span class="text-red-500">*</span></label>
                            <textarea name="deskripsi_lengkap" required rows="3" placeholder="Detail lengkap barang temuan..." class="w-full bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition resize-none"><?= htmlspecialchars($editItem['deskripsi_lengkap'] ?? '') ?></textarea>
                        </div>
                        <?php if (!$editItem): ?>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-2">Foto Barang <span class="text-gray-400 font-normal">(Opsional)</span></label>
                            <div class="flex items-center gap-3">
                                <div id="foto-preview-box" class="hidden w-20 h-20 rounded-xl overflow-hidden border-2 border-blue-300">
                                    <img id="foto-preview-img" src="" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex gap-2">
                                    <input type="file" id="foto-main" name="foto" accept="image/*" class="hidden" onchange="previewBarang(this)"/>
                                    <label for="foto-main" class="cursor-pointer flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                                        <i data-lucide="image" class="w-4 h-4"></i> Pilih Foto
                                    </label>
                                    <label class="cursor-pointer flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                                        <input type="file" accept="image/*" capture="environment" class="hidden" onchange="copyBarangKamera(this)"/>
                                        <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                                    </label>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="pt-4 border-t">
                            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow hover:bg-blue-700 transition">
                                <?= $editItem ? '💾 Simpan Perubahan' : '+ Tambah Barang' ?>
                            </button>
                            <a href="tambah_barang.php?view=daftar_barang" class="ml-3 text-sm text-gray-500 hover:text-gray-700">Batal</a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <?php include 'includes/bottom_nav.php'; ?>
    </div>
</div>

<script>
function previewBarang(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('foto-preview-img').src = e.target.result;
            document.getElementById('foto-preview-box').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function copyBarangKamera(input) {
    if (input.files && input.files[0]) {
        const dt = new DataTransfer();
        dt.items.add(input.files[0]);
        document.getElementById('foto-main').files = dt.files;
        previewBarang(input);
    }
}
function validateBarangForm(e) {
    const req = ['nama_barang','lokasi','waktu_ditemukan','deskripsi_singkat','deskripsi_lengkap'];
    for (const r of req) {
        const el = document.querySelector(`[name="${r}"]`);
        if (!el || !el.value.trim()) {
            e.preventDefault();
            alert(`Kolom wajib belum terisi!`);
            el && el.focus();
            return false;
        }
    }
    return true;
}
lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
