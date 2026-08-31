<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <RouterLink to="/" class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">OI</div>
          <div>
            <p class="text-lg font-semibold tracking-tight">ORMAWA ITI</p>
            <p class="text-sm text-slate-500">Portal organisasi mahasiswa</p>
          </div>
        </RouterLink>

        <nav class="hidden items-center gap-6 md:flex">
          <RouterLink to="/" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Home</RouterLink>
          <RouterLink to="/portal/organizations" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Organizations</RouterLink>
          <RouterLink to="/portal/news" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">News</RouterLink>
          <RouterLink to="/portal/events" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Events</RouterLink>
          <RouterLink to="/portal/about" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">About</RouterLink>
          <RouterLink to="/portal/contact" class="text-sm font-medium text-slate-600 transition hover:text-slate-900">Contact</RouterLink>
        </nav>

        <RouterLink to="/portal/organizations" class="hidden rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:opacity-90 md:inline-flex">
          Browse Organizations
        </RouterLink>
      </div>
    </header>

    <main>
      <slot />
    </main>

    <footer class="border-t border-slate-200 bg-white">
      <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-3 lg:px-8">
        <div>
          <p class="text-lg font-semibold">ORMAWA ITI</p>
          <p class="mt-3 text-sm leading-7 text-slate-600">Portal utama untuk menjelajahi setiap organisasi mahasiswa di Institut Teknologi Indonesia.</p>
        </div>
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Quick Links</p>
          <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li><RouterLink to="/portal/organizations">Organizations</RouterLink></li>
            <li><RouterLink to="/portal/news">News</RouterLink></li>
            <li><RouterLink to="/portal/events">Events</RouterLink></li>
          </ul>
        </div>
        <div>
          <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Contact</p>
          <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li>Institut Teknologi Indonesia</li>
            <li>ormawa@iti.ac.id</li>
          </ul>
        </div>
      </div>
      <div class="border-t border-slate-200 bg-slate-50/70 px-4 py-4 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
        © {{ new Date().getFullYear() }} ORMAWA ITI
      </div>
    </footer>
  </div>
</template>
