<?php
include 'includes/header.php';
require_once 'includes/db.php';

if ($userRole !== 'petugas' && $userRole !== 'admin') {
    header("Location: index.php"); exit;
}

$itemId = intval($_GET['item_id'] ?? 0);

// Fetch item info
$item = null;
if ($itemId > 0) {
    try {
        $item = $pdo->prepare("SELECT * FROM found_items WHERE id = ?");
        $item->execute([$itemId]);
        $item = $item->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengambil = trim($_POST['pengambil'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $fakultas = trim($_POST['fakultas'] ?? '');
    $petugas = trim($_POST['petugas'] ?? '');
    $jam = trim($_POST['jam'] ?? '');
    $foundItemId = intval($_POST['found_item_id'] ?? $itemId);

    // Handle KTP photo
    $fotoKtp = 'no-photo.jpg';
    if (isset($_FILES['foto_ktp']) && $_FILES['foto_ktp']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir('./uploads')) mkdir('./uploads', 0755, true);
        $ext = strtolower(pathinfo($_FILES['foto_ktp']['name'], PATHINFO_EXTENSION));
        $fn = 'ktp_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['foto_ktp']['tmp_name'], './uploads/' . $fn);
        $fotoKtp = './uploads/' . $fn;
    }

    // Handle Bukti photo
    $fotoBukti = 'no-photo.jpg';
    if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir('./uploads')) mkdir('./uploads', 0755, true);
        $ext = strtolower(pathinfo($_FILES['foto_bukti']['name'], PATHINFO_EXTENSION));
        $fn = 'bukti_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['foto_bukti']['tmp_name'], './uploads/' . $fn);
        $fotoBukti = './uploads/' . $fn;
    }

    try {
        $pdo->prepare("INSERT INTO pickups (found_item_id, nama_pengambil, nim, fakultas, nama_petugas, waktu_pengambilan, foto_ktp, foto_bukti) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$foundItemId, $pengambil, $nim, $fakultas, $petugas, $jam, $fotoKtp, $fotoBukti]);

        $pdo->prepare("UPDATE found_items SET status = 'taken' WHERE id = ?")->execute([$foundItemId]);
        
        header("Location: index.php?tab=taken&msg=verify_success"); exit;
    } catch(PDOException $e) {
        $error = "Kesalahan: " . $e->getMessage();
    }
}

// Fetch all available items for dropdown if no specific item
$availableItems = [];
try {
    $stmtAv = $pdo->query("SELECT id, nama_barang, lokasi FROM found_items WHERE status = 'available' ORDER BY id DESC");
    $availableItems = $stmtAv->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {}
?>

<div class="flex h-screen overflow-hidden bg-[#1A202C]">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col md:ml-64 relative min-h-screen overflow-y-auto">
        <?php include 'includes/top_header.php'; ?>
        
        <div class="flex-1 relative z-20 px-0 md:px-8 pb-24 md:pb-8">
            <div class="bg-[#F3F4F6] flex-1 rounded-t-3xl md:rounded-3xl w-full max-w-7xl mx-auto md:mx-0 relative z-20 px-5 md:px-8 pt-6 md:pt-8 pb-8 min-h-[70vh] shadow-2xl">
                <a href="index.php?tab=available" class="flex items-center text-gray-500 hover:text-blue-600 transition text-sm mb-6 font-medium w-fit">
                    <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i> Kembali
                </a>

                <div class="max-w-2xl mx-auto">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2 text-center">Verifikasi Pengambilan</h2>
                    <p class="text-sm text-center text-gray-500 mb-8">Isi data serah terima barang. Kolom <span class="text-red-500 font-bold">*</span> wajib diisi.</p>

                    <?php if($item): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
                        <i data-lucide="package" class="w-5 h-5 text-blue-600 shrink-0"></i>
                        <div>
                            <p class="text-sm font-bold text-blue-800">Barang: <?= htmlspecialchars($item['nama_barang']) ?></p>
                            <p class="text-xs text-blue-600"><?= htmlspecialchars($item['lokasi']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if(isset($error)): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 text-sm font-semibold border border-red-200"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="verify_pickup.php" enctype="multipart/form-data" class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 space-y-5" onsubmit="return validatePickupForm(event)">
                        <input type="hidden" name="found_item_id" value="<?= $itemId ?>">
                        
                        <?php if(!$item && !empty($availableItems)): ?>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Pilih Barang <span class="text-red-500">*</span></label>
                            <select name="found_item_id" required class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition">
                                <option value="">-- Pilih Barang Temuan --</option>
                                <?php foreach($availableItems as $av): ?>
                                <option value="<?= $av['id'] ?>"><?= htmlspecialchars($av['nama_barang']) ?> - <?= htmlspecialchars($av['lokasi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Pengambil <span class="text-red-500">*</span></label>
                                <input type="text" name="pengambil" required placeholder="Nama Lengkap" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">NIM / NIP <span class="text-red-500">*</span></label>
                                <input type="text" name="nim" required placeholder="Nomor Induk" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Fakultas / Jurusan <span class="text-red-500">*</span></label>
                                <input type="text" name="fakultas" required placeholder="contoh: Teknik Informatika" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Petugas <span class="text-red-500">*</span></label>
                                <input type="text" name="petugas" required placeholder="Nama Petugas Jaga" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Jam Pengambilan <span class="text-red-500">*</span></label>
                                <input type="text" name="jam" required placeholder="contoh: 27 Juni 2026, 14:00 WIB" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                        </div>

                        <!-- Foto Upload Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-3">Foto KTM Pengambil <span class="text-red-500">*</span></label>
                                <div id="preview-ktp-container" class="hidden w-full h-32 rounded-2xl overflow-hidden border-2 border-blue-300 mb-2">
                                    <img id="preview-ktp" src="" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex gap-2">
                                    <input type="file" id="foto_ktp_input" name="foto_ktp" accept="image/*" class="hidden" onchange="previewImg(this,'preview-ktp','preview-ktp-container')" />
                                    <label for="foto_ktp_input" class="flex-1 h-16 border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition text-xs text-gray-500 gap-2">
                                        <i data-lucide="image" class="w-4 h-4"></i> Galeri
                                    </label>
                                    <label class="flex-1 h-16 border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition text-xs text-gray-500 gap-2">
                                        <input type="file" accept="image/*" capture="environment" class="hidden" onchange="copyToMain(this, 'foto_ktp_input', 'preview-ktp', 'preview-ktp-container')" />
                                        <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-3">Foto Pemilik + Barang <span class="text-red-500">*</span></label>
                                <div id="preview-bukti-container" class="hidden w-full h-32 rounded-2xl overflow-hidden border-2 border-blue-300 mb-2">
                                    <img id="preview-bukti" src="" class="w-full h-full object-cover" />
                                </div>
                                <div class="flex gap-2">
                                    <input type="file" id="foto_bukti_input" name="foto_bukti" accept="image/*" class="hidden" onchange="previewImg(this,'preview-bukti','preview-bukti-container')" />
                                    <label for="foto_bukti_input" class="flex-1 h-16 border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition text-xs text-gray-500 gap-2">
                                        <i data-lucide="image" class="w-4 h-4"></i> Galeri
                                    </label>
                                    <label class="flex-1 h-16 border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition text-xs text-gray-500 gap-2">
                                        <input type="file" accept="image/*" capture="environment" class="hidden" onchange="copyToMain(this, 'foto_bukti_input', 'preview-bukti', 'preview-bukti-container')" />
                                        <i data-lucide="camera" class="w-4 h-4"></i> Kamera
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100">
                            <button type="submit" class="w-full bg-green-600 text-white py-3.5 rounded-2xl font-bold shadow-md hover:bg-green-700 transition transform hover:-translate-y-0.5">
                                ✓ Submit Verifikasi Pengambilan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include 'includes/bottom_nav.php'; ?>
    </div>
</div>

<script>
function previewImg(input, previewId, containerId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(containerId).classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function copyToMain(input, mainInputId, previewId, containerId) {
    if (input.files && input.files[0]) {
        const dt = new DataTransfer();
        dt.items.add(input.files[0]);
        document.getElementById(mainInputId).files = dt.files;
        previewImg(input, previewId, containerId);
    }
}

function validatePickupForm(e) {
    const fields = [
        {name:'pengambil', label:'Nama Pengambil'},
        {name:'nim', label:'NIM/NIP'},
        {name:'fakultas', label:'Fakultas/Jurusan'},
        {name:'petugas', label:'Nama Petugas'},
        {name:'jam', label:'Jam Pengambilan'},
    ];
    for (const f of fields) {
        const el = document.querySelector(`[name="${f.name}"]`);
        if (!el || !el.value.trim()) {
            e.preventDefault();
            alert(`Kolom "${f.label}" wajib diisi!`);
            el && el.focus();
            return false;
        }
    }
    return true;
}
lucide.createIcons();
</script>

<?php include 'includes/footer.php'; ?>
