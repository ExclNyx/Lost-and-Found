<?php
include 'includes/header.php';
require_once 'includes/db.php';

$tab    = $_GET['tab']    ?? 'available';
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    * { font-family: 'Inter', sans-serif; }

    .card-item {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .card-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }
    .card-item img {
        transition: transform 0.4s ease;
    }
    .card-item:hover img {
        transform: scale(1.06);
    }
    .tab-pill { transition: all 0.2s ease; }
    .fade-in {
        animation: fadeInUp 0.4s ease both;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .badge-available { background: linear-gradient(135deg, #10b981, #059669); }
    .badge-valuable  { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
</style>

<div class="flex h-screen overflow-hidden bg-[#1A202C]">
    <?php include 'includes/sidebar.php'; ?>

    <div class="flex-1 flex flex-col md:ml-64 relative min-h-screen overflow-y-auto">
        <!-- Subtle pattern -->
        <div class="absolute inset-0 z-0 pointer-events-none" style="height:320px; background: linear-gradient(180deg, rgba(59,130,246,0.08) 0%, transparent 100%);"></div>

        <?php include 'includes/top_header.php'; ?>

        <div class="flex-1 relative z-10 px-4 md:px-8 pb-24 md:pb-10">

            <!-- Success banner -->
            <?php if(isset($_GET['msg'])): ?>
            <div class="mb-4 bg-emerald-500 text-white px-5 py-3 rounded-2xl text-sm font-semibold flex items-center gap-2 shadow-lg fade-in">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <?= $_GET['msg'] === 'report_success' ? 'Laporan barang hilang berhasil dikirim!' : 'Pengambilan barang berhasil diverifikasi!' ?>
            </div>
            <?php endif; ?>

            <?php if ($tab === 'laporan'): ?>
            <!-- ====== LAPORAN ====== -->
            <div class="fade-in">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-3">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-white">Laporan Kehilangan</h2>
                        <p class="text-sm text-blue-300 mt-0.5">Semua laporan yang masuk ke sistem</p>
                    </div>
                    <?php if ($userRole === 'mahasiswa'): ?>
                    <a href="form_report.php" class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg transition">
                        <i data-lucide="plus" class="w-4 h-4"></i> Buat Laporan
                    </a>
                    <?php endif; ?>
                </div>

                <?php
                $lostReports = [];
                try {
                    if ($userRole === 'mahasiswa') {
                        $stmt = $pdo->prepare("SELECT lr.*, u.nama as reporter, u.foto_profil FROM lost_reports lr JOIN users u ON lr.user_id = u.id WHERE lr.user_id = ? ORDER BY lr.id DESC");
                        $stmt->execute([$_SESSION['userId']]);
                    } else {
                        $stmt = $pdo->query("SELECT lr.*, u.nama as reporter, u.foto_profil FROM lost_reports lr JOIN users u ON lr.user_id = u.id ORDER BY lr.id DESC");
                    }
                    $lostReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {}
                ?>

                <?php if (empty($lostReports)): ?>
                <div class="bg-white bg-opacity-5 border border-white border-opacity-10 rounded-3xl py-16 text-center">
                    <i data-lucide="file-x" class="w-12 h-12 mx-auto text-blue-300 opacity-40 mb-3"></i>
                    <p class="text-white font-semibold opacity-60">Belum ada laporan kehilangan</p>
                    <?php if($userRole==='mahasiswa'): ?><p class="text-sm text-blue-300 opacity-40 mt-1">Klik tombol "Buat Laporan" di atas</p><?php endif; ?>
                </div>
                <?php else: ?>
                <div class="space-y-3 max-w-3xl">
                    <?php foreach($lostReports as $r): ?>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition fade-in">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-sm shrink-0" style="background: linear-gradient(135deg,#3b82f6,#6366f1);">
                                    <?= strtoupper(substr($r['reporter'],0,1)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($r['reporter']) ?></p>
                                    <p class="text-xs text-gray-400">📞 <?= htmlspecialchars($r['kontak']) ?></p>
                                </div>
                            </div>
                            <span class="shrink-0 text-[10px] px-3 py-1 rounded-full font-bold <?= $r['status_verifikasi'] ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' ?>">
                                <?= $r['status_verifikasi'] ? '✓ Terverifikasi' : '⏳ Menunggu' ?>
                            </span>
                        </div>
                        <div class="mt-3 pl-13 space-y-1">
                            <p class="font-bold text-slate-800">🔍 <?= htmlspecialchars($r['nama_barang']) ?></p>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars(substr($r['deskripsi'],0,120)) ?>...</p>
                            <div class="flex flex-wrap gap-3 text-xs text-gray-400 mt-2">
                                <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i><?= htmlspecialchars($r['lokasi_hilang']) ?></span>
                                <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i><?= htmlspecialchars($r['waktu_hilang']) ?></span>
                            </div>
                        </div>
                        <?php if ($userRole === 'petugas'): ?>
                        <div class="mt-3 flex justify-end border-t pt-3">
                            <a href="laporan_action.php?action=<?= $r['status_verifikasi'] ? 'unverify' : 'verify' ?>&id=<?= $r['id'] ?>" class="text-xs font-bold px-4 py-1.5 rounded-full text-white transition <?= $r['status_verifikasi'] ? 'bg-gray-400 hover:bg-gray-500' : 'bg-green-500 hover:bg-green-600' ?>">
                                <?= $r['status_verifikasi'] ? 'Batal Verifikasi' : '✓ Verifikasi' ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($tab === 'taken'): ?>
            <!-- ====== TAKEN ====== -->
            <div class="fade-in">
                <div class="mb-6">
                    <h2 class="text-xl md:text-2xl font-bold text-white">Barang Sudah Diambil</h2>
                    <p class="text-sm text-blue-300 mt-0.5">Daftar barang yang sudah berhasil dikembalikan</p>
                </div>

                <?php
                $takenItems = [];
                try {
                    $sqlT = "SELECT fi.*, p.nama_pengambil, p.nim, p.nama_petugas, p.waktu_pengambilan FROM found_items fi LEFT JOIN pickups p ON fi.id = p.found_item_id WHERE fi.status = 'taken'";
                    if (!empty($search)) $sqlT .= " AND (fi.nama_barang LIKE :s OR fi.lokasi LIKE :s)";
                    $sqlT .= " ORDER BY fi.id DESC";
                    $stT = $pdo->prepare($sqlT);
                    if (!empty($search)) $stT->execute([':s' => "%$search%"]);
                    else $stT->execute();
                    $takenItems = $stT->fetchAll(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {}
                ?>

                <?php if(empty($takenItems)): ?>
                <div class="bg-white bg-opacity-5 border border-white border-opacity-10 rounded-3xl py-16 text-center">
                    <i data-lucide="package-check" class="w-12 h-12 mx-auto text-blue-300 opacity-40 mb-3"></i>
                    <p class="text-white font-semibold opacity-60">Belum ada barang yang diambil</p>
                </div>
                <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach($takenItems as $item): ?>
                    <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 card-item fade-in">
                        <div class="relative overflow-hidden h-40">
                            <img src="<?= htmlspecialchars($item['foto'] ?? 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=200&fit=crop') ?>" class="w-full h-full object-cover opacity-50" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                            <span class="absolute top-3 right-3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full">TAKEN</span>
                            <p class="absolute bottom-3 left-3 text-white font-bold text-sm"><?= htmlspecialchars($item['nama_barang']) ?></p>
                        </div>
                        <div class="p-4">
                            <p class="text-xs text-gray-400 flex items-center gap-1 mb-3"><i data-lucide="map-pin" class="w-3 h-3"></i><?= htmlspecialchars(substr($item['lokasi'],0,40)) ?></p>
                            <?php if($item['nama_pengambil']): ?>
                            <div class="border-t pt-3 text-xs text-gray-600 space-y-1.5">
                                <p class="flex items-center gap-2"><i data-lucide="user" class="w-3 h-3 text-blue-500"></i><?= htmlspecialchars($item['nama_pengambil']) ?></p>
                                <p class="flex items-center gap-2"><i data-lucide="shield-check" class="w-3 h-3 text-green-500"></i><?= htmlspecialchars($item['nama_petugas']) ?></p>
                                <p class="flex items-center gap-2"><i data-lucide="clock" class="w-3 h-3 text-amber-500"></i><?= htmlspecialchars($item['waktu_pengambilan']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php else: ?>
            <!-- ====== AVAILABLE ====== -->
            <div class="fade-in">
                <!-- Header beda per role -->
                <?php if ($userRole === 'mahasiswa'): ?>
                <div class="mb-6">
                    <h2 class="text-xl md:text-2xl font-bold text-white">Cari Barang Kamu 🔍</h2>
                    <p class="text-sm text-blue-300 mt-1">Daftar barang temuan yang masih menunggu pemiliknya.</p>
                </div>
                <?php elseif ($userRole === 'petugas'): ?>
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-3">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-white">Barang Temuan</h2>
                        <p class="text-sm text-blue-300 mt-0.5">Kelola barang yang belum diambil</p>
                    </div>
                    <a href="tambah_barang.php?view=tambah" class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg transition">
                        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Barang
                    </a>
                </div>
                <?php endif; ?>

                <!-- Filter pills -->
                <div class="flex items-center gap-2 mb-5 overflow-x-auto pb-1 scrollbar-hide">
                    <?php
                    $pills = ['all'=>'Semua', 'non-valuable'=>'Non Valuable', 'valuable'=>'Valuable'];
                    $activeClasses = 'bg-white text-slate-800 shadow-md font-bold';
                    $inactiveClasses = 'bg-white bg-opacity-10 text-white hover:bg-opacity-20 font-medium';
                    foreach ($pills as $val => $label):
                    ?>
                    <a href="index.php?tab=available&filter=<?= $val ?>&search=<?= urlencode($search) ?>"
                       class="tab-pill shrink-0 px-4 py-2 rounded-full text-sm whitespace-nowrap <?= $filter===$val ? $activeClasses : $inactiveClasses ?>">
                        <?= $label ?>
                    </a>
                    <?php endforeach; ?>
                    <?php if (!empty($search)): ?>
                    <span class="shrink-0 px-3 py-1.5 rounded-full bg-blue-500 text-white text-xs font-semibold flex items-center gap-1">
                        "<?= htmlspecialchars($search) ?>"
                        <a href="index.php?tab=available&filter=<?= $filter ?>"><i data-lucide="x" class="w-3 h-3"></i></a>
                    </span>
                    <?php endif; ?>
                </div>

                <?php
                $foundItems = [];
                try {
                    $sql = "SELECT * FROM found_items WHERE status = 'available'";
                    $params = [];
                    if ($filter !== 'all') { $sql .= " AND kategori = :kat"; $params[':kat'] = $filter; }
                    if (!empty($search)) { $sql .= " AND (nama_barang LIKE :s OR deskripsi_singkat LIKE :s OR lokasi LIKE :s)"; $params[':s'] = "%$search%"; }
                    $sql .= " ORDER BY id DESC";
                    $stI = $pdo->prepare($sql);
                    $stI->execute($params);
                    $foundItems = $stI->fetchAll(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {}
                ?>

                <?php if(empty($foundItems)): ?>
                <div class="bg-white bg-opacity-5 border border-white border-opacity-10 rounded-3xl py-16 text-center">
                    <i data-lucide="package-search" class="w-12 h-12 mx-auto text-blue-300 opacity-40 mb-3"></i>
                    <p class="text-white font-semibold opacity-60">Tidak ada barang ditemukan</p>
                    <p class="text-sm text-blue-300 opacity-40 mt-1">Coba ubah filter atau kata kunci pencarian</p>
                </div>
                <?php else: ?>
                <!-- GRID CARDS -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                    <?php foreach($foundItems as $idx => $item): ?>
                    <a href="detail_item.php?id=<?= $item['id'] ?>" class="card-item bg-white rounded-2xl overflow-hidden shadow-sm block" style="animation-delay: <?= $idx * 0.06 ?>s;">
                        <!-- Gambar -->
                        <div class="relative overflow-hidden" style="height: 140px;">
                            <img
                                src="<?= htmlspecialchars($item['foto'] ?: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=280&fit=crop') ?>"
                                class="w-full h-full object-cover"
                                loading="lazy"
                            />
                            <!-- Gradient overlay -->
                            <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(0,0,0,0.55) 0%, transparent 60%);"></div>

                            <!-- Badge kategori (kiri atas) -->
                            <span class="absolute top-2 left-2 text-white text-[9px] font-bold px-2 py-0.5 rounded-full <?= $item['kategori']==='valuable' ? 'badge-valuable' : 'badge-available' ?>">
                                <?= $item['kategori']==='valuable' ? '⭐ Valuable' : 'Non-Val' ?>
                            </span>

                            <!-- Badge available (kanan atas) -->
                            <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-green-400 shadow-lg shadow-green-400/50" title="Available"></span>

                            <!-- Petugas CTA -->
                            <?php if($userRole === 'petugas'): ?>
                            <a href="verify_pickup.php?item_id=<?= $item['id'] ?>"
                               onclick="event.stopPropagation()"
                               class="absolute bottom-2 right-2 bg-green-500 hover:bg-green-600 text-white text-[9px] font-bold px-2.5 py-1 rounded-full transition shadow-md">
                               Verifikasi
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div class="p-3">
                            <h3 class="font-bold text-slate-800 text-xs md:text-sm leading-snug line-clamp-1 mb-1">
                                <?= htmlspecialchars($item['nama_barang']) ?>
                            </h3>
                            <p class="text-[10px] md:text-xs text-gray-400 line-clamp-2 leading-relaxed mb-2">
                                <?= htmlspecialchars($item['deskripsi_singkat']) ?>
                            </p>
                            <div class="flex items-center gap-1 text-[9px] md:text-[10px] text-gray-400 font-medium">
                                <i data-lucide="map-pin" class="w-3 h-3 shrink-0 text-blue-400"></i>
                                <span class="truncate"><?= htmlspecialchars($item['lokasi']) ?></span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Mahasiswa: CTA lapor -->
                <?php if($userRole === 'mahasiswa' && !empty($foundItems)): ?>
                <div class="mt-8 bg-white bg-opacity-5 border border-white border-opacity-10 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div>
                        <p class="text-white font-semibold text-sm">Barang kamu tidak ada di sini?</p>
                        <p class="text-blue-300 text-xs mt-0.5">Buat laporan kehilangan agar petugas bisa membantu.</p>
                    </div>
                    <a href="form_report.php" class="shrink-0 bg-blue-500 hover:bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-lg">
                        + Lapor Sekarang
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </div><!-- /content -->

        <?php include 'includes/bottom_nav.php'; ?>
    </div>
</div>

<script>lucide.createIcons();</script>
<?php include 'includes/footer.php'; ?>
