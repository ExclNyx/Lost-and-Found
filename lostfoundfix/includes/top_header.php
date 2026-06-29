<div class="pt-6 px-4 pb-6 w-full max-w-7xl mx-auto relative z-10 md:px-8">
  <div class="flex items-center gap-3 mb-6">
    <!-- Logo Mobile -->
    <div class="md:hidden bg-white w-12 h-12 rounded-full flex items-center justify-center font-bold text-[10px] text-center leading-tight shadow-lg shrink-0">
      LOST<br/>&<br/>FOUND
    </div>
    
    <!-- Top Nav Pill -->
    <div class="bg-white flex-1 md:flex-none w-full md:w-auto rounded-full px-2 py-2 flex items-center justify-between shadow-lg md:max-w-2xl md:mx-auto gap-2">
      <div class="flex space-x-1 md:space-x-2 px-1 md:px-4 text-xs md:text-sm font-semibold text-gray-700 overflow-x-auto scrollbar-hide whitespace-nowrap">
        <?php $tab = $_GET['tab'] ?? 'available'; ?>
        <a href="index.php?tab=laporan" class="px-3 py-1.5 rounded-full transition <?= $tab === 'laporan' ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700' ?>">Laporan</a>
        <a href="index.php?tab=available" class="px-3 py-1.5 rounded-full transition <?= $tab === 'available' ? 'bg-green-500 text-white' : 'hover:bg-gray-100 text-gray-700' ?>">Available</a>
        <a href="index.php?tab=taken" class="px-3 py-1.5 rounded-full transition <?= $tab === 'taken' ? 'bg-red-500 text-white' : 'hover:bg-gray-100 text-gray-700' ?>">Taken</a>
      </div>
      <form method="GET" action="index.php" class="relative shrink-0">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($_GET['tab'] ?? 'available') ?>" />
        <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>" />
        <input 
          type="text" 
          name="search"
          value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
          placeholder="Search..." 
          class="bg-gray-100 rounded-full px-3 md:px-5 py-1.5 md:py-2 text-xs md:text-sm w-24 md:w-48 outline-none focus:ring-2 focus:ring-blue-200 transition-all"
        />
        <button type="submit" class="absolute right-3 top-1.5 md:top-2">
          <i data-lucide="search" class="w-3 md:w-4 h-3 md:h-4 text-gray-400 hover:text-blue-500 transition"></i>
        </button>
      </form>
    </div>
  </div>
  
  <?php if($currentScreen == 'index'): ?>
  <div class="text-white text-left md:text-center mt-4 md:mt-8">
    <h1 class="text-xl md:text-4xl font-semibold leading-tight">
      Temukan <span class="font-bold text-blue-300">Barang Anda Disini</span><br/>
      Lalu Konfirmasi di Pos Terdekat!
    </h1>
  </div>
  <?php endif; ?>
</div>
