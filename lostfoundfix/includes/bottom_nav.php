<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t rounded-t-3xl shadow-[0_-5px_15px_rgba(0,0,0,0.05)] z-50 flex justify-around py-3 px-4">
  <?php $tab = $_GET['tab'] ?? 'available'; ?>
  <a href="index.php?tab=available" class="flex flex-col items-center <?= ($currentScreen == 'index' && $tab === 'available') ? 'text-green-600' : 'text-gray-500' ?> hover:text-green-600 transition">
    <i data-lucide="package" class="w-6 h-6 mb-1"></i>
    <span class="text-[10px] font-medium">Available</span>
  </a>
  <a href="index.php?tab=laporan" class="flex flex-col items-center <?= ($currentScreen == 'index' && $tab === 'laporan') ? 'text-blue-600' : 'text-gray-500' ?> hover:text-blue-600 transition">
    <i data-lucide="file-text" class="w-6 h-6 mb-1"></i>
    <span class="text-[10px] font-medium">Laporan</span>
  </a>
  <?php if($userRole === 'petugas'): ?>
  <a href="tambah_barang.php?view=tambah" class="flex flex-col items-center text-gray-500 hover:text-blue-600 transition">
    <i data-lucide="plus-circle" class="w-6 h-6 mb-1"></i>
    <span class="text-[10px] font-medium">Tambah</span>
  </a>
  <?php endif; ?>
  <a href="index.php?tab=taken" class="flex flex-col items-center <?= ($currentScreen == 'index' && $tab === 'taken') ? 'text-red-500' : 'text-gray-500' ?> hover:text-red-500 transition">
    <i data-lucide="package-check" class="w-6 h-6 mb-1"></i>
    <span class="text-[10px] font-medium">Taken</span>
  </a>
  <a href="profile.php" class="flex flex-col items-center <?= $currentScreen == 'profile' ? 'text-blue-600' : 'text-gray-500' ?> hover:text-blue-600 transition">
    <i data-lucide="user" class="w-6 h-6 mb-1"></i>
    <span class="text-[10px] font-medium">Profil</span>
  </a>
</div>
