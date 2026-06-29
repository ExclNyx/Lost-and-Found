<?php
include 'includes/header.php';
// We will try to include db.php to connect to database.
// If user hasn't imported it yet, we handle errors gracefully.
$db_connected = false;
if (file_exists('includes/db.php')) {
    include 'includes/db.php';
    if (isset($pdo)) {
        $db_connected = true;
    }
}

// Default Data
$userData = [
    'nama' => $userRole === 'mahasiswa' ? "Indah Sekali" : "Petugas Jaga",
    'email' => $userRole === 'mahasiswa' ? "indah@webmail.umm.ac.id" : "petugas@umm.ac.id",
    'foto_profil' => 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&q=80&w=200&h=200'
];

$userId = $_SESSION['userId'] ?? 1;
$pesan = '';

// Load data from Database if connected
if ($db_connected) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $userData = $row;
        }
    } catch(PDOException $e) {
        // Fallback to default
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBaru = $_POST['nama'] ?? $userData['nama'];
    $emailBaru = $_POST['email'] ?? $userData['email'];
    $fotoBaru = $userData['foto_profil'];

    // Handle File Upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto']['tmp_name'];
        $fileName = $_FILES['foto']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = './uploads/';
        
        // Ensure upload directory exists
        if(!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $newFileName;
        
        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            $fotoBaru = $dest_path; // Update photo path
        }
    }

    // Update in DB
    if ($db_connected) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, foto_profil = ? WHERE id = ?");
            if($stmt->execute([$namaBaru, $emailBaru, $fotoBaru, $userId])) {
                $pesan = "Profil berhasil diperbarui!";
                // Update local variable to show immediately
                $userData['nama'] = $namaBaru;
                $userData['email'] = $emailBaru;
                $userData['foto_profil'] = $fotoBaru;
            }
        } catch(PDOException $e) {
            $pesan = "Gagal memperbarui database: " . $e->getMessage();
        }
    } else {
        $pesan = "Profil berhasil diperbarui (Simulasi - Database belum terhubung).";
        $userData['nama'] = $namaBaru;
        $userData['email'] = $emailBaru;
        $userData['foto_profil'] = $fotoBaru;
    }
}
?>

<div class="flex h-screen overflow-hidden bg-[#1A202C]">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="flex-1 flex flex-col md:ml-64 relative min-h-screen overflow-y-auto">
        <?php include 'includes/top_header.php'; ?>
        
        <div class="flex-1 relative z-20 px-0 md:px-8 pb-24 md:pb-8">
            <div class="bg-[#F3F4F6] flex-1 rounded-t-3xl md:rounded-3xl w-full max-w-7xl mx-auto md:mx-0 relative z-20 px-5 md:px-8 pt-6 md:pt-8 pb-8 min-h-[70vh] shadow-2xl transition-all duration-300">
                <a href="index.php" class="flex items-center text-gray-500 hover:text-blue-600 transition text-sm mb-6 font-medium w-fit">
                    <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i> Kembali
                </a>

                <div class="flex flex-col items-center pt-4 md:pt-12 max-w-lg mx-auto">
                    
                    <?php if($pesan): ?>
                        <div class="w-full bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 relative" role="alert">
                            <span class="block sm:inline"><?= htmlspecialchars($pesan) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST" enctype="multipart/form-data" class="w-full flex flex-col items-center">
                        <!-- Profile Image Section -->
                        <div class="relative mb-10 group">
                            <img src="<?= htmlspecialchars($userData['foto_profil']) ?>" alt="Profile" class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover border-4 border-white shadow-xl transition transform group-hover:scale-105" id="preview-image" />
                            
                            <!-- Hidden File Input -->
                            <input type="file" id="foto" name="foto" accept="image/*" class="hidden" onchange="previewFile()" />
                            
                            <!-- Custom Label to trigger file input -->
                            <label for="foto" class="absolute bottom-1 right-1 bg-white p-2.5 rounded-full shadow-lg border border-gray-100 hover:bg-gray-50 transition cursor-pointer">
                                <i data-lucide="camera" class="w-5 h-5 text-slate-700"></i>
                            </label>
                        </div>

                        <!-- Data Fields -->
                        <div class="w-full space-y-5 bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100">
                            <div>
                                <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                    <i data-lucide="user" class="w-4 h-4 mr-2"></i> Nama Lengkap
                                </label>
                                <input type="text" name="nama" required placeholder="Nama Lengkap" value="<?= htmlspecialchars($userData['nama']) ?>" class="w-full bg-gray-50 rounded-xl px-4 py-3 shadow-inner border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500 transition" />
                            </div>
                            <div>
                                <label class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Email
                                </label>
                                <input type="email" name="email" required placeholder="Email@webmail.umm.ac.id" value="<?= htmlspecialchars($userData['email']) ?>" class="w-full bg-gray-50 rounded-xl px-4 py-3 shadow-inner border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500 transition" />
                                <p class="text-xs text-gray-400 mt-1 ml-1">Email universitas Anda.</p>
                            </div>

                            <div class="pt-6 flex justify-center">
                                <button type="submit" class="bg-slate-800 text-white px-10 py-3 rounded-2xl font-bold shadow-md hover:bg-slate-900 transition w-full md:w-auto transform hover:-translate-y-0.5">
                                    Simpan Perubahan
                                </button>
                            </div>
                            
                            <div class="relative py-8 flex items-center">
                                <div class="flex-grow border-t border-gray-200"></div>
                                <span class="flex-shrink-0 mx-4 text-gray-400 text-xs font-medium uppercase tracking-wider">Akses & Bantuan</span>
                                <div class="flex-grow border-t border-gray-200"></div>
                            </div>

                            <div class="flex flex-col md:flex-row justify-center gap-4">
                                <?php if($userRole === 'mahasiswa'): ?>
                                    <!-- Tombol Contact Petugas via WhatsApp -->
                                    <a href="https://wa.me/6281236336675" target="_blank" class="bg-[#25D366] text-white px-8 py-3 rounded-2xl font-bold shadow-md flex items-center justify-center hover:bg-[#20bd5a] transition">
                                        <i data-lucide="message-circle" class="w-5 h-5 mr-2"></i> Contact Petugas
                                    </a>
                                <?php else: ?>
                                    <!-- Tampilan Role untuk selain mahasiswa -->
                                    <div class="bg-[#00B14F] text-white px-8 py-3 rounded-2xl font-bold shadow-md flex items-center justify-center transition">
                                        <i data-lucide="user" class="w-5 h-5 mr-2"></i> Role: <?= ucfirst($userRole) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Tombol Logout Mobile. Desktop logout via Sidebar -->
                                <a href="login.php?action=logout" class="md:hidden bg-red-50 text-red-600 px-8 py-3 rounded-2xl font-bold shadow-sm hover:bg-red-100 flex items-center justify-center transition">
                                    <i data-lucide="log-out" class="w-5 h-5 mr-2"></i> Keluar Akun
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include 'includes/bottom_nav.php'; ?>
    </div>
</div>

<script>
// Script to preview selected image before upload
function previewFile() {
    const preview = document.getElementById('preview-image');
    const file = document.querySelector('input[type=file]').files[0];
    const reader = new FileReader();

    reader.onloadend = function () {
        preview.src = reader.result;
    }

    if (file) {
        reader.readAsDataURL(file);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
