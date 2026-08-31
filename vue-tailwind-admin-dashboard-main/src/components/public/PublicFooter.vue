<template>
  <footer class="bg-[#191C1D] text-[#C6C5CF] pt-14 pb-10 text-xs border-t border-slate-800" role="contentinfo">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">
        
        <!-- Col 1: Identity & Mission (4 Cols) -->
        <div class="md:col-span-4 space-y-4">
          <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded bg-white overflow-hidden flex items-center justify-center p-0.5 shadow-2xs shrink-0">
              <img src="/images/logo/iti-logo.png" alt="Institut Teknologi Indonesia" class="h-full w-full object-contain" />
            </div>
            <div>
              <span class="text-sm font-bold text-white tracking-tight block">ORMAWA ITI</span>
              <span class="text-[10px] text-[#A0A0A8] block">Institut Teknologi Indonesia</span>
            </div>
          </div>
          <p class="text-[#A0A0A8] text-xs leading-relaxed max-w-sm">
            Portal publik resmi tata kelola, warta berita, agenda kegiatan, dan dokumen seluruh organisasi kemahasiswaan di lingkungan Institut Teknologi Indonesia.
          </p>
        </div>

        <!-- Col 2: Navigation (2 Cols) -->
        <div class="md:col-span-2 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Navigasi Publik</h4>
          <ul class="space-y-2">
            <li><router-link to="/" class="hover:text-white transition duration-150">Beranda</router-link></li>
            <li><router-link to="/organizations" class="hover:text-white transition duration-150">Direktori Ormawa</router-link></li>
            <li><a href="/#kabar-terbaru" class="hover:text-white transition duration-150">Warta Mahasiswa</a></li>
            <li><a href="/#agenda-kampus" class="hover:text-white transition duration-150">Agenda Kegiatan</a></li>
          </ul>
        </div>

        <!-- Col 3: Organization Types (3 Cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Kategori Organisasi</h4>
          <ul class="space-y-2">
            <li><router-link to="/organizations" class="hover:text-white transition duration-150">Himpunan Mahasiswa (HMPS)</router-link></li>
            <li><router-link to="/organizations" class="hover:text-white transition duration-150">Unit Kegiatan Mahasiswa (UKM)</router-link></li>
            <li><router-link to="/organizations" class="hover:text-white transition duration-150">Badan Eksekutif Mahasiswa (BEM)</router-link></li>
            <li><router-link to="/login" class="hover:text-white transition duration-150">Portal Login Pengurus</router-link></li>
          </ul>
        </div>

        <!-- Col 4: Contact & Campus Address (3 Cols) -->
        <div class="md:col-span-3 space-y-3">
          <h4 class="text-white font-bold uppercase tracking-wider text-[11px]">Institut Teknologi Indonesia</h4>
          <p class="leading-relaxed text-[#A0A0A8] text-xs">
            Jl. Raya Puspiptek Serpong, Tangerang Selatan, Banten 15314.<br />
            Pusat Kemahasiswaan & Alumni (PKA) ITI
          </p>
          <div class="pt-1">
            <a href="https://iti.ac.id" target="_blank" rel="noopener" class="text-[#ABC7FF] hover:text-white transition duration-150 font-semibold inline-flex items-center gap-1">
              Situs Resmi ITI &rarr;
            </a>
          </div>
        </div>
      </div>

      <!-- Bottom Strip -->
      <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row justify-between items-center gap-3 text-[11px] text-[#737783]">
        <p>&copy; {{ new Date().getFullYear() }} Institut Teknologi Indonesia. Hak Cipta Dilindungi.</p>
        <p>Sistem Informasi Manajemen Organisasi Kemahasiswaan</p>
      </div>
    </div>
  </footer>
</template>
