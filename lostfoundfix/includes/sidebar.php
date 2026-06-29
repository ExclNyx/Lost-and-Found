<div class="hidden md:flex w-64 bg-white shadow-xl flex-col h-full fixed left-0 top-0 z-50">
  <div class="p-6 flex items-center justify-center border-b">
    <div class="bg-[#1A202C] text-white w-16 h-16 rounded-full flex items-center justify-center font-bold text-xs text-center leading-tight shadow-lg">
      LOST<br/>&<br/>FOUND
    </div>
  </div>
  
  <div class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
    <?php $tab = $_GET['tab'] ?? 'available'; ?>
    <a href="index.php?tab=available" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?= ($currentScreen == 'index' && $tab === 'available') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-50' ?>">
      <i data-lucide="package" class="w-5 h-5"></i>
      Available
    </a>
    <a href="index.php?tab=laporan" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?= ($currentScreen == 'index' && $tab === 'laporan') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' ?>">
      <i data-lucide="file-text" class="w-5 h-5"></i>
      <?= $userRole === 'petugas' ? 'Laporan Masuk' : 'Laporan Saya' ?>
    </a>
    <a href="index.php?tab=taken" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?= ($currentScreen == 'index' && $tab === 'taken') ? 'bg-red-50 text-red-600' : 'text-gray-600 hover:bg-gray-50' ?>">
      <i data-lucide="package-check" class="w-5 h-5"></i>
      Taken
    </a>
    
    <?php if($userRole === 'petugas'): ?>
    <div class="pt-2 pb-1">
      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-4 mb-1">Petugas</p>
    </div>
    <a href="tambah_barang.php?view=laporan_masuk" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?= $currentScreen == 'tambah_barang' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' ?>">
      <i data-lucide="clipboard-list" class="w-5 h-5"></i>
      Kelola Laporan
    </a>
    <a href="verify_pickup.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?= $currentScreen == 'verify_pickup' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' ?>">
      <i data-lucide="check-square" class="w-5 h-5"></i>
      Verifikasi Ambil
    </a>
    <?php endif; ?>
    
    <div class="pt-2 pb-1">
      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-4 mb-1">Akun</p>
    </div>
    <a href="profile.php" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?= $currentScreen == 'profile' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' ?>">
      <i data-lucide="user" class="w-5 h-5"></i>
      Profil Saya
    </a>
    <?php if($userRole == 'admin'): ?>
    <a href="admin_dashboard.php?view=overview" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition <?= $currentScreen == 'admin_dashboard' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' ?>">
      <i data-lucide="settings" class="w-5 h-5"></i>
      Dashboard Admin
    </a>
    <?php endif; ?>
  </div>

  <div class="p-4 border-t">
    <a href="login.php?action=logout" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-red-600 hover:bg-red-50 transition">
      <i data-lucide="log-out" class="w-5 h-5"></i>
      Keluar
    </a>
  </div>
</div>
