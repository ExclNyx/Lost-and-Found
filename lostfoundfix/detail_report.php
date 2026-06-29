<?php
include 'includes/header.php';
include 'data/mock_data.php';

$reportId = $_GET['id'] ?? 1;
$selectedReport = null;
foreach ($mockLostReports as $report) {
    if ($report['id'] == $reportId) {
        $selectedReport = $report;
        break;
    }
}

if (!$selectedReport) {
    echo "Report not found.";
    exit;
}

$data = $selectedReport['fullData'] ?? [
    'name' => $selectedReport['reporter'], 
    'item' => 'Barang Tidak Diketahui', 
    'location' => 'Area UMM', 
    'time' => '-', 
    'desc' => $selectedReport['text'], 
    'contact' => '-', 
    'attachment' => '-'
];
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

                <div class="max-w-3xl mx-auto">
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-2">Laporan Barang Hilang</h2>
                    <p class="text-sm md:text-base text-gray-500 mb-8">Detail informasi barang hilang yang dilaporkan.</p>
                    
                    <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Nama Pelapor</h3>
                                <p class="text-base text-slate-800 font-medium"><?= htmlspecialchars($data['name']) ?></p>
                            </div>
                            <div>
                                <h3 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Telah Kehilangan Barang</h3>
                                <p class="text-base text-slate-800 font-medium"><?= htmlspecialchars($data['item']) ?></p>
                            </div>
                            <div>
                                <h3 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Lokasi Hilang</h3>
                                <p class="text-base text-slate-800 font-medium"><?= htmlspecialchars($data['location']) ?></p>
                            </div>
                            <div>
                                <h3 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Tanggal & Jam Hilang</h3>
                                <p class="text-base text-slate-800 font-medium"><?= htmlspecialchars($data['time']) ?></p>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">Deskripsi Barang</h3>
                            <p class="text-base text-slate-800 leading-relaxed text-justify bg-gray-50 p-4 rounded-xl"><?= htmlspecialchars($data['desc']) ?></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 pt-6">
                            <div>
                                <h3 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Hubungi</h3>
                                <p class="text-base text-blue-600 font-bold"><?= htmlspecialchars($data['contact']) ?></p>
                            </div>
                            <div>
                                <h3 class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Lampiran</h3>
                                <p class="text-sm text-slate-800"><?= htmlspecialchars($data['attachment']) ?></p>
                                <p class="text-xs text-gray-500 mt-1">Nama Pada KTM dan KTP: <span class="font-bold text-slate-700"><?= htmlspecialchars($data['name']) ?></span></p>
                            </div>
                        </div>

                        <div class="pt-8 flex justify-center md:justify-end border-t border-gray-100">
                            <?php if($userRole === 'petugas'): ?>
                            <a href="detail_report.php?id=<?= $reportId ?>&action=verify" 
                               class="inline-block px-8 py-3 rounded-xl text-white font-bold shadow-md transition transform hover:-translate-y-0.5 <?= $selectedReport['verified'] ? 'bg-red-500 hover:bg-red-600' : 'bg-[#00B14F] hover:bg-green-600' ?>">
                                <?= $selectedReport['verified'] ? 'Batalkan Verifikasi' : 'Verifikasi Laporan Ini' ?>
                            </a>
                            <?php else: ?>
                            <div class="px-8 py-3 rounded-xl text-white font-bold shadow-sm <?= $selectedReport['verified'] ? 'bg-green-500' : 'bg-gray-400' ?>">
                                <?= $selectedReport['verified'] ? '✓ Terverifikasi (Diproses)' : 'Menunggu Verifikasi' ?>
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
