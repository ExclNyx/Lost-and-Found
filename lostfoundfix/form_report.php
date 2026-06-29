<?php
include 'includes/header.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['userId'] ?? 1;
    $namaBarang = trim($_POST['barang'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $waktu = trim($_POST['tanggal'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kontak = trim($_POST['hp'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $namaLengkap = trim($_POST['nama'] ?? '');
    
    // Handle file upload
    $fotoPath = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            if (!is_dir('./uploads')) mkdir('./uploads', 0755, true);
            $newName = 'report_' . time() . '_' . rand(100,999) . '.' . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], './uploads/' . $newName);
            $fotoPath = './uploads/' . $newName;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO lost_reports (user_id, nama_barang, lokasi_hilang, waktu_hilang, deskripsi, kontak, lampiran, status_verifikasi) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$userId, $namaBarang, $lokasi, $waktu, $deskripsi, $kontak, "NIM: $nim | Nama: $namaLengkap"]);
        header("Location: index.php?tab=laporan&msg=report_success");
        exit;
    } catch(PDOException $e) {
        $error = "Gagal menyimpan laporan: " . $e->getMessage();
    }
}
?>

<div class="flex h-screen overflow-hidden bg-[#1A202C]">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col md:ml-64 relative min-h-screen overflow-y-auto">
        <?php include 'includes/top_header.php'; ?>
        
        <div class="flex-1 relative z-20 px-0 md:px-8 pb-24 md:pb-8">
            <div class="bg-[#F3F4F6] flex-1 rounded-t-3xl md:rounded-3xl w-full max-w-7xl mx-auto md:mx-0 relative z-20 px-5 md:px-8 pt-6 md:pt-8 pb-8 min-h-[70vh] shadow-2xl">
                <a href="index.php?tab=laporan" class="flex items-center text-gray-500 hover:text-blue-600 transition text-sm mb-6 font-medium w-fit">
                    <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i> Kembali ke Laporan
                </a>

                <div class="max-w-2xl mx-auto">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2 text-center">Lapor Barang Hilang</h2>
                    <p class="text-sm text-center text-gray-500 mb-8">Isi semua kolom berlabel <span class="text-red-500 font-bold">*</span> dengan lengkap dan benar.</p>
                    
                    <?php if(isset($error)): ?>
                    <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-4 text-sm font-semibold border border-red-200 text-center">
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="form_report.php" enctype="multipart/form-data" class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 space-y-5" novalidate onsubmit="return validateForm(event)">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Pelapor <span class="text-red-500">*</span></label>
                                <input type="text" name="nama" required placeholder="Nama Lengkap" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">NIM / NIP <span class="text-red-500">*</span></label>
                                <input type="text" name="nim" required placeholder="contoh: 20241037xxx" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
                                <input type="tel" name="hp" required placeholder="08xxxxxxxxxx" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
                                <input type="text" name="barang" required placeholder="contoh: Dompet Hitam" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Lokasi Hilang <span class="text-red-500">*</span></label>
                                <input type="text" name="lokasi" required placeholder="contoh: Tangga GKB 2 Lantai 3" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1.5">Tanggal & Jam Hilang <span class="text-red-500">*</span></label>
                                <input type="text" name="tanggal" required placeholder="contoh: 27 Juni 2026, 10:00" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 transition" />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1.5">Deskripsi Barang <span class="text-red-500">*</span></label>
                            <textarea name="deskripsi" required placeholder="Jelaskan ciri-ciri barang secara detail: warna, merek, isi, kondisi, dll." rows="4" class="w-full bg-gray-50 rounded-2xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500 border border-gray-200 resize-none transition"></textarea>
                        </div>
                        
                        <!-- Upload Foto -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-3">Foto Barang <span class="text-gray-400 font-normal">(Opsional)</span></label>

                            <!-- Preview -->
                            <div id="preview-container" class="hidden mb-3 relative w-full max-w-xs">
                                <img id="preview-img" src="" class="w-full h-36 object-cover rounded-2xl border-2 border-blue-300" />
                                <button type="button" onclick="hapusFoto()" class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs flex items-center justify-center shadow">&times;</button>
                            </div>

                            <!-- 2 Tombol Terpisah -->
                            <div class="flex gap-3">
                                <!-- ===== GALERI: TIDAK pakai capture ===== -->
                                <input type="file" id="foto-galeri" name="foto" accept="image/*" class="hidden" onchange="previewFoto(this)" />
                                <label for="foto-galeri" class="flex-1 flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-300 rounded-2xl py-5 cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition group">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 group-hover:bg-blue-100 flex items-center justify-center transition">
                                        <i data-lucide="image" class="w-5 h-5 text-gray-500 group-hover:text-blue-600"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-500 group-hover:text-blue-600">Pilih dari Galeri</span>
                                </label>

                                <!-- ===== KAMERA: pakai capture=environment ===== -->
                                <input type="file" id="foto-kamera" accept="image/*" capture="environment" class="hidden" onchange="pindahKeUtama(this)" />
                                <label for="foto-kamera" class="flex-1 flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-400 rounded-2xl py-5 cursor-pointer hover:border-slate-600 hover:bg-slate-50 transition group">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-slate-200 flex items-center justify-center transition">
                                        <i data-lucide="camera" class="w-5 h-5 text-slate-500 group-hover:text-slate-700"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500 group-hover:text-slate-700">Buka Kamera</span>
                                </label>
                            </div>
                            <p id="nama-file" class="text-[10px] text-gray-400 mt-2 hidden"></p>
                            <p class="text-[10px] text-gray-400 mt-2">* Tombol Kamera akan langsung membuka kamera HP Anda.</p>
                        </div>

                        <div class="pt-6 border-t border-gray-100">
                            <button type="submit" class="w-full bg-blue-600 text-white py-3.5 rounded-2xl font-bold shadow-md hover:bg-blue-700 transition transform hover:-translate-y-0.5 text-center">
                                Submit Laporan
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
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('preview-container').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
        const nf = document.getElementById('nama-file');
        nf.textContent = '📎 ' + input.files[0].name;
        nf.classList.remove('hidden');
    }
}

function pindahKeUtama(input) {
    if (input.files && input.files[0]) {
        // Salin hasil kamera ke input galeri agar ikut ter-submit
        const dt = new DataTransfer();
        dt.items.add(input.files[0]);
        document.getElementById('foto-galeri').files = dt.files;
        previewFoto({ files: input.files });
    }
}

function hapusFoto() {
    document.getElementById('foto-galeri').value = '';
    document.getElementById('preview-img').src = '';
    document.getElementById('preview-container').classList.add('hidden');
    document.getElementById('nama-file').classList.add('hidden');
}

function validateForm(e) {
    const fields = [
        { name: 'nama', label: 'Nama Pelapor' },
        { name: 'nim', label: 'NIM/NIP' },
        { name: 'hp', label: 'Nomor HP' },
        { name: 'barang', label: 'Nama Barang' },
        { name: 'lokasi', label: 'Lokasi Hilang' },
        { name: 'tanggal', label: 'Tanggal Hilang' },
        { name: 'deskripsi', label: 'Deskripsi Barang' },
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
