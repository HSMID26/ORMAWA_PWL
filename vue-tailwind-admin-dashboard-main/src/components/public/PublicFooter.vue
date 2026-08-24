<template>
  <footer class="bg-slate-950 text-slate-400 pt-12 pb-8 text-xs border-t border-slate-900" role="contentinfo">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
        
        <!-- Col 1: Identity -->
        <div class="md:col-span-5 space-y-3">
          <div class="flex items-center gap-2.5">
            <div class="h-7 w-7 bg-blue-700 text-white rounded flex items-center justify-center font-bold text-xs tracking-wider">
              ITI
            </div>
            <div>
              <span class="text-sm font-bold text-white tracking-tight block">CMS ORMAWA</span>
              <span class="text-[10px] text-slate-400 block">Institut Teknologi Indonesia</span>
            </div>
          </div>
          <p class="text-slate-400 text-xs leading-relaxed max-w-sm">
            Portal resmi informasi, kegiatan, dan tata kelola himpunan mahasiswa serta unit kegiatan mahasiswa Institut Teknologi Indonesia.
          </p>
        </div>

        <!-- Col 2: Navigation -->
        <div class="md:col-span-3 space-y-2.5">
          <h4 class="text-white font-bold uppercase tracking-wider text-[10px]">Navigasi Publik</h4>
          <ul class="space-y-1.5">
            <li><router-link to="/" class="hover:text-white transition">Beranda Utama</router-link></li>
            <li><router-link to="/organizations" class="hover:text-white transition">Direktori Ormawa</router-link></li>
            <li><router-link to="/register" class="hover:text-white transition">Pendaftaran Organisasi</router-link></li>
            <li><router-link to="/login" class="hover:text-white transition">Portal Login Pengurus</router-link></li>
          </ul>
        </div>

        <!-- Col 3: Contact & Address -->
        <div class="md:col-span-4 space-y-2.5">
          <h4 class="text-white font-bold uppercase tracking-wider text-[10px]">Institut Teknologi Indonesia</h4>
          <p class="leading-relaxed text-slate-400 text-xs">
            Jl. Raya Puspiptek Serpong, Tangerang Selatan, Banten 15314.<br />
            Pusat Kemahasiswaan & Alumni (PKA) ITI
          </p>
          <div class="pt-1">
            <a href="https://iti.ac.id" target="_blank" rel="noopener" class="text-slate-300 hover:text-white transition font-medium">
              Situs Resmi ITI &rarr;
            </a>
          </div>
        </div>
      </div>

      <!-- Bottom Strip -->
      <div class="pt-6 border-t border-slate-900 flex flex-col sm:flex-row justify-between items-center gap-3 text-[11px] text-slate-500">
        <p>&copy; {{ new Date().getFullYear() }} Institut Teknologi Indonesia. Hak Cipta Dilindungi.</p>
        <p>Platform Tata Kelola Kemahasiswaan</p>
      </div>
    </div>
  </footer>
</template>
