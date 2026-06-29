<?php
include 'includes/header.php';
include 'data/mock_data.php';

$itemId = $_GET['id'] ?? 1;
$selectedItem = null;
foreach ($mockFoundItems as $item) {
    if ($item['id'] == $itemId) {
        $selectedItem = $item;
        break;
    }
}

if (!$selectedItem) {
    echo "Item not found.";
    exit;
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

                <div class="flex flex-col md:flex-row gap-8">
                    <div class="w-full md:w-1/2">
                        <img src="<?= $selectedItem['fullImage'] ?? $selectedItem['image'] ?>" alt="<?= htmlspecialchars($selectedItem['name']) ?>" class="w-full h-64 md:h-[500px] object-cover rounded-3xl shadow-lg border border-gray-100" />
                    </div>
                    
                    <div class="w-full md:w-1/2 flex flex-col">
                        <div class="mb-6">
                            <span class="inline-block px-4 py-1.5 rounded-full text-xs font-bold text-white shadow-sm mb-4 <?= $selectedItem['status'] === 'taken' ? 'bg-red-500' : 'bg-[#00B14F]' ?>">
                                <?= $selectedItem['status'] === 'taken' ? 'Taken' : 'Available' ?>
                            </span>
                            <h1 class="text-2xl md:text-4xl font-bold text-slate-800 mb-2"><?= htmlspecialchars($selectedItem['name']) ?></h1>
                        </div>
                        
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-sm text-gray-500 font-semibold uppercase tracking-wider mb-1">Lokasi Temuan, Jam & Tanggal</h2>
                                <p class="text-base text-slate-800 font-medium"><?= htmlspecialchars($selectedItem['location'] ?? 'Area Kampus UMM') ?></p>
                            </div>
                            
                            <div>
                                <h2 class="text-sm text-gray-500 font-semibold uppercase tracking-wider mb-1">Deskripsi</h2>
                                <p class="text-base text-slate-800 leading-relaxed"><?= htmlspecialchars($selectedItem['fullDesc'] ?? $selectedItem['desc']) ?></p>
                            </div>
                            
                            <div>
                                <h2 class="text-sm text-gray-500 font-semibold uppercase tracking-wider mb-1">Waktu Post</h2>
                                <p class="text-base text-slate-800 font-medium">15 Hari yang Lalu (19 Mei 2026)</p>
                            </div>
                            
                            <?php if($selectedItem['status'] === 'taken'): ?>
                            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mt-6">
                                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Informasi Pengambilan</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <h2 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Waktu Ambil</h2>
                                        <p class="text-sm text-slate-800 font-medium">15 Hari yang Lalu</p>
                                    </div>
                                    <div>
                                        <h2 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Diambil Oleh</h2>
                                        <p class="text-sm text-slate-800 font-medium">NIM: 20241037011000</p>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h2 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">Foto Bukti Pengambilan</h2>
                                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200&h=200" alt="Pemilik" class="w-32 h-32 md:w-48 md:h-48 object-cover rounded-2xl shadow-sm border" />
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'includes/bottom_nav.php'; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
