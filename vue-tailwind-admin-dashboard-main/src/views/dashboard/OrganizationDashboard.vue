<template>
  <AdminLayout>
    <!-- Loading Skeleton -->
    <div v-if="isLoading" class="space-y-6">
      <div class="h-24 w-full animate-pulse rounded-2xl bg-gray-200 dark:bg-gray-800"></div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-32 animate-pulse rounded-2xl bg-gray-200 dark:bg-gray-800"></div>
      </div>
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="h-80 animate-pulse rounded-2xl bg-gray-200 dark:bg-gray-800 lg:col-span-2"></div>
        <div class="h-80 animate-pulse rounded-2xl bg-gray-200 dark:bg-gray-800"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-center dark:border-rose-900/50 dark:bg-rose-950/20">
      <AlertTriangleIcon class="mx-auto h-10 w-10 text-rose-500 mb-2" />
      <h3 class="text-base font-semibold text-rose-800 dark:text-rose-400">Gagal Memuat Dashboard</h3>
      <p class="mt-1 text-sm text-rose-600 dark:text-rose-300">{{ errorMessage }}</p>
      <button @click="loadDashboard" class="mt-4 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700">
        Coba Lagi
      </button>
    </div>

    <div v-else-if="summary" class="space-y-6">
      <!-- ─── 1. Header Banner Organisasi & Status Peran ───────────────────────────── -->
      <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
          <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400 font-bold text-xl border border-brand-100 dark:border-brand-900/30">
              <span v-if="summary.organization.logo">
                <img :src="summary.organization.logo" :alt="summary.organization.nama" class="h-14 w-14 rounded-2xl object-cover" />
              </span>
              <span v-else>
                {{ summary.organization.nama.substring(0, 2).toUpperCase() }}
              </span>
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                  {{ summary.organization.nama }}
                </h1>
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                  {{ summary.organization.jenis }}
                </span>
                <span
                  class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                  :class="{
                    'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400': authStore.role === 'Admin Organisasi',
                    'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400': authStore.role === 'Editor',
                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400': authStore.role === 'Kontributor'
                  }"
                >
                  Peran: {{ authStore.role }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-2 flex-wrap">
                <span>Subdomain: <span class="font-mono text-gray-700 dark:text-gray-300 font-semibold">{{ summary.organization.subdomain }}</span></span>
                <span>•</span>
                <span v-if="summary.period" class="inline-flex items-center gap-1 font-medium text-brand-600 dark:text-brand-400">
                  <CalendarClockIcon class="h-3.5 w-3.5" />
                  {{ summary.period.period_name }} ({{ formatDate(summary.period.start_date) }} - {{ formatDate(summary.period.end_date) }})
                </span>
                <span v-else class="text-amber-500 font-medium">
                  Belum ada periode aktif
                </span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <router-link
              to="/organization/posts/create"
              class="inline-flex items-center gap-1.5 rounded-lg bg-brand-600 px-3.5 py-2 text-xs font-medium text-white hover:bg-brand-700 shadow-sm transition"
            >
              <PlusIcon class="h-4 w-4" />
              Tulis Artikel Baru
            </router-link>
            <router-link
              v-if="authStore.role !== 'Kontributor'"
              to="/organization/agenda/create"
              class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition"
            >
              <CalendarIcon class="h-4 w-4" />
              Tambah Agenda
            </router-link>
          </div>
        </div>

        <!-- Period Expiry Warning (For Admin Organisasi) -->
        <div
          v-if="authStore.role === 'Admin Organisasi' && summary.period && summary.period.remaining_days <= 30"
          class="mt-4 flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-300"
        >
          <div class="flex items-center gap-2">
            <AlertTriangleIcon class="h-4 w-4 shrink-0 text-amber-600" />
            <span>
              Periode kepengurusan Anda tersisa <strong class="font-bold">{{ summary.period.remaining_days }} hari</strong>. Segera ajukan perpanjangan jika masa kepengurusan akan berganti.
            </span>
          </div>
          <router-link to="/organization/period" class="font-semibold underline shrink-0 hover:text-amber-900">
            Ajukan Renewal →
          </router-link>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════════════════ -->
      <!-- WORKSPACE 1: KONTRIBUTOR VIEW                                              -->
      <!-- ═══════════════════════════════════════════════════════════════════════════ -->
      <template v-if="authStore.role === 'Kontributor'">
        <!-- Contributor Banner -->
        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/40 dark:bg-amber-950/20">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white font-bold">
              <FileTextIcon class="h-5 w-5" />
            </div>
            <div>
              <h2 class="text-sm font-bold text-amber-900 dark:text-amber-300">Ruang Kerja Kontributor</h2>
              <p class="text-xs text-amber-700 dark:text-amber-400">
                Tulis artikel berkualitas untuk organisasi Anda. Seluruh artikel yang Anda kirimkan akan melalui proses review oleh tim editorial dan Admin Organisasi sebelum dipublikasikan.
              </p>
            </div>
          </div>
        </div>

        <!-- Contributor Metrics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold text-gray-500">Total Artikel Saya</span>
            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ summary.my_posts?.total ?? 0 }}</div>
          </div>
          <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold text-gray-500">Draft Tersimpan</span>
            <div class="mt-2 text-2xl font-bold text-gray-700 dark:text-gray-300">{{ summary.my_posts?.draft ?? 0 }}</div>
          </div>
          <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold text-amber-600">Menunggu Review</span>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ summary.my_posts?.review ?? 0 }}</div>
          </div>
          <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold text-emerald-600">Dipublikasikan</span>
            <div class="mt-2 text-2xl font-bold text-emerald-600">{{ summary.my_posts?.published ?? 0 }}</div>
          </div>
          <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <span class="text-xs font-semibold text-rose-600">Perlu Revisi (Ditolak)</span>
            <div class="mt-2 text-2xl font-bold text-rose-600">{{ summary.my_posts?.rejected ?? 0 }}</div>
          </div>
        </div>

        <!-- Contributor Recent Articles Table -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-bold text-gray-900 dark:text-white">Artikel Saya Terbaru</h2>
              <p class="text-xs text-gray-500">Riwayat artikel yang telah Anda buat beserta status peninjauan.</p>
            </div>
            <router-link to="/organization/posts" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
              Lihat Semua Artikel →
            </router-link>
          </div>

          <div v-if="summary.my_recent_posts && summary.my_recent_posts.length > 0" class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800 text-[11px] font-bold uppercase text-gray-500">
                  <th class="py-2.5 px-3">Judul Artikel</th>
                  <th class="py-2.5 px-3">Status</th>
                  <th class="py-2.5 px-3">Terakhir Diperbarui</th>
                  <th class="py-2.5 px-3 text-right">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <tr v-for="post in summary.my_recent_posts" :key="post.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                  <td class="py-3 px-3 font-semibold text-gray-900 dark:text-white">{{ post.judul }}</td>
                  <td class="py-3 px-3">
                    <span
                      class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                      :class="{
                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10': post.status === 'published',
                        'bg-amber-50 text-amber-700 dark:bg-amber-500/10': post.status === 'review',
                        'bg-gray-100 text-gray-600 dark:bg-gray-800': post.status === 'draft',
                        'bg-rose-50 text-rose-700 dark:bg-rose-500/10': post.status === 'rejected',
                      }"
                    >
                      {{ post.status }}
                    </span>
                  </td>
                  <td class="py-3 px-3 text-gray-500">{{ formatDate(post.updated_at) }}</td>
                  <td class="py-3 px-3 text-right">
                    <router-link
                      :to="`/organization/posts/edit/${post.id}`"
                      class="rounded-lg bg-gray-100 dark:bg-gray-800 px-2.5 py-1 font-semibold text-gray-700 dark:text-gray-300 hover:bg-brand-50 hover:text-brand-600"
                    >
                      Edit
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="py-8 text-center text-xs text-gray-400">
            Anda belum membuat artikel. Klik tombol "Tulis Artikel Baru" di atas untuk mulai menulis.
          </div>
        </div>
      </template>

      <!-- ═══════════════════════════════════════════════════════════════════════════ -->
      <!-- WORKSPACE 2: EDITOR & ADMIN ORGANISASI VIEW                                -->
      <!-- ═══════════════════════════════════════════════════════════════════════════ -->
      <template v-else>
        <!-- ─── Key Metrics Grid ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Metric: Posts -->
          <router-link
            to="/organization/posts"
            class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Artikel & Berita
              </span>
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                <FileTextIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ summary.posts.total }}
              </div>
              <div class="mt-2 flex flex-wrap gap-1 text-[11px]">
                <span class="rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                  {{ summary.posts.published }} Published
                </span>
                <span v-if="summary.posts.review > 0" class="rounded bg-amber-50 px-1.5 py-0.5 font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                  {{ summary.posts.review }} Review
                </span>
                <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                  {{ summary.posts.draft }} Draft
                </span>
              </div>
            </div>
          </router-link>

          <!-- Metric: Agenda -->
          <router-link
            to="/organization/agenda"
            class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Agenda Acara
              </span>
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <CalendarIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ summary.agenda.total }}
              </div>
              <div class="mt-2 flex flex-wrap gap-1 text-[11px]">
                <span class="rounded bg-brand-50 px-1.5 py-0.5 font-medium text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                  {{ summary.agenda.upcoming }} Mendatang
                </span>
                <span v-if="summary.agenda.today > 0" class="rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                  {{ summary.agenda.today }} Hari Ini
                </span>
                <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                  {{ summary.agenda.past }} Riwayat
                </span>
              </div>
            </div>
          </router-link>

          <!-- Metric: Announcements -->
          <router-link
            to="/organization/announcements"
            class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Pengumuman
              </span>
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                <MegaphoneIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ summary.announcements.total }}
              </div>
              <div class="mt-2 flex flex-wrap gap-1 text-[11px]">
                <span class="rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                  {{ summary.announcements.active }} Aktif
                </span>
                <span v-if="summary.announcements.urgent > 0" class="rounded bg-rose-50 px-1.5 py-0.5 font-medium text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                  {{ summary.announcements.urgent }} Urgent
                </span>
              </div>
            </div>
          </router-link>

          <!-- Metric: Members (Admin Organisasi) vs Gallery/Docs (Editor) -->
          <router-link
            v-if="authStore.role === 'Admin Organisasi'"
            to="/organization/users"
            class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Pengguna Organisasi
              </span>
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-500/10 dark:text-purple-400">
                <UsersIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3">
              <div class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ summary.members.total_users }}
              </div>
              <div class="mt-2 flex flex-wrap gap-1 text-[11px]">
                <span class="rounded bg-blue-50 px-1.5 py-0.5 font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                  {{ summary.members.total_editors ?? 0 }} Editor
                </span>
                <span class="rounded bg-amber-50 px-1.5 py-0.5 font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                  {{ summary.members.total_contributors ?? 0 }} Kontributor
                </span>
              </div>
            </div>
          </router-link>

          <router-link
            v-else
            to="/organization/gallery"
            class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-sm hover:border-brand-300 hover:shadow-md transition dark:border-gray-800 dark:bg-gray-900"
          >
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                Media & Dokumen
              </span>
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                <ImageIcon class="h-5 w-5" />
              </div>
            </div>
            <div class="mt-3">
              <div class="text-lg font-bold text-gray-900 dark:text-white">
                Media Library
              </div>
              <p class="text-xs text-gray-500 mt-1">Kelola arsip foto, dokumentasi, dan berkas organisasi.</p>
            </div>
          </router-link>
        </div>

        <!-- ─── Main Two-Column Content Area ──────────────────────────────────────── -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Kolom Kiri (2 Kolom): Upcoming Agendas & Recent Activities -->
          <div class="space-y-6 lg:col-span-2">
            <!-- Upcoming Agendas -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <CalendarIcon class="h-4 w-4 text-brand-500" />
                    Agenda Acara Terdekat
                  </h2>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Jadwal kegiatan organisasi dalam waktu dekat.
                  </p>
                </div>
                <router-link to="/organization/agenda" class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                  Lihat Semua →
                </router-link>
              </div>

              <div v-if="summary.agenda.upcoming_list.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
                <div
                  v-for="item in summary.agenda.upcoming_list"
                  :key="item.id"
                  class="flex items-start gap-4 py-3.5 first:pt-0 last:pb-0"
                >
                  <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-400 border border-brand-100 dark:border-brand-900/30">
                    <span class="text-sm font-bold leading-none">{{ getDay(item.tanggal_pelaksanaan) }}</span>
                    <span class="text-[10px] uppercase font-semibold mt-0.5 leading-none">{{ getMonth(item.tanggal_pelaksanaan) }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                      {{ item.judul }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5">
                      {{ item.deskripsi }}
                    </p>
                  </div>
                  <router-link
                    :to="`/organization/agenda`"
                    class="shrink-0 rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                  >
                    Detail
                  </router-link>
                </div>
              </div>
              <div v-else class="py-8 text-center text-xs text-gray-400">
                Belum ada agenda mendatang dalam waktu dekat.
              </div>
            </div>

            <!-- Recent Activity Log (Admin Organisasi only) -->
            <div v-if="authStore.role === 'Admin Organisasi'" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <HistoryIcon class="h-4 w-4 text-brand-500" />
                    Aktivitas Terakhir
                  </h2>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Audit trail mutasi data dan aksi oleh anggota organisasi.
                  </p>
                </div>
                <router-link to="/organization/activity-logs" class="text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                  Semua Log →
                </router-link>
              </div>

              <div v-if="summary.recent_activities.length > 0" class="divide-y divide-gray-100 dark:divide-gray-800">
                <div
                  v-for="log in summary.recent_activities"
                  :key="log.id"
                  class="flex items-start gap-3.5 py-3 first:pt-0 last:pb-0"
                >
                  <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300">
                    {{ (log.user?.name || 'S').substring(0, 2).toUpperCase() }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-gray-800 dark:text-gray-200">
                      <span class="font-semibold text-gray-900 dark:text-white">{{ log.user?.name || 'Sistem' }}</span>
                      {{ ' ' + log.description }}
                    </p>
                    <span class="text-[11px] text-gray-400 mt-0.5 block">
                      {{ formatDate(log.created_at) }}
                    </span>
                  </div>
                  <span class="rounded px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    {{ log.module }}
                  </span>
                </div>
              </div>
              <div v-else class="py-8 text-center text-xs text-gray-400">
                Belum ada riwayat aktivitas yang tercatat.
              </div>
            </div>
          </div>

          <!-- Kolom Kanan (1 Kolom): Quick Actions & Public Profile Status -->
          <div class="space-y-6">
            <!-- Quick Action Buttons -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
              <h2 class="text-base font-bold text-gray-900 dark:text-white mb-3">
                Aksi Cepat
              </h2>
              <div class="grid grid-cols-2 gap-3">
                <router-link
                  to="/organization/posts/create"
                  class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-gray-200 p-3.5 text-center hover:border-brand-300 hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-950/20 transition group"
                >
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600 group-hover:scale-110 transition">
                    <FileTextIcon class="h-4 w-4" />
                  </div>
                  <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">Tulis Berita</span>
                </router-link>

                <router-link
                  to="/organization/agenda/create"
                  class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-gray-200 p-3.5 text-center hover:border-brand-300 hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-950/20 transition group"
                >
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 group-hover:scale-110 transition">
                    <CalendarIcon class="h-4 w-4" />
                  </div>
                  <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">Buat Agenda</span>
                </router-link>

                <router-link
                  to="/organization/announcements"
                  class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-gray-200 p-3.5 text-center hover:border-brand-300 hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-950/20 transition group"
                >
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 group-hover:scale-110 transition">
                    <MegaphoneIcon class="h-4 w-4" />
                  </div>
                  <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">Pengumuman</span>
                </router-link>

                <router-link
                  to="/organization/gallery"
                  class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-gray-200 p-3.5 text-center hover:border-brand-300 hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-950/20 transition group"
                >
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 group-hover:scale-110 transition">
                    <ImageIcon class="h-4 w-4" />
                  </div>
                  <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">Media Galeri</span>
                </router-link>

                <template v-if="authStore.role === 'Admin Organisasi'">
                  <router-link
                    to="/organization/users"
                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-gray-200 p-3.5 text-center hover:border-brand-300 hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-950/20 transition group"
                  >
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 text-purple-600 group-hover:scale-110 transition">
                      <UsersIcon class="h-4 w-4" />
                    </div>
                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">Pengguna</span>
                  </router-link>

                  <router-link
                    to="/organization/period"
                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-gray-200 p-3.5 text-center hover:border-brand-300 hover:bg-brand-50/50 dark:border-gray-800 dark:hover:bg-brand-950/20 transition group"
                  >
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 group-hover:scale-110 transition">
                      <CalendarClockIcon class="h-4 w-4" />
                    </div>
                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">Periode Renewal</span>
                  </router-link>
                </template>
              </div>
            </div>

            <!-- Website Info Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
              <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">
                Website Organisasi
              </h2>
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Website publik organisasi Anda dapat diakses oleh civitas akademika dan umum.
              </p>

              <div class="space-y-2.5 text-xs">
                <div class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-800">
                  <span class="text-gray-500">Status Portal:</span>
                  <span class="font-semibold text-emerald-600 dark:text-emerald-400">Aktif & Publik</span>
                </div>
                <div class="flex justify-between items-center py-1.5 border-b border-gray-100 dark:border-gray-800">
                  <span class="text-gray-500">Warna Aksen:</span>
                  <div class="flex items-center gap-2">
                    <span class="h-3.5 w-3.5 rounded-full border border-gray-300" :style="{ backgroundColor: summary.organization.warna_tema }"></span>
                    <span class="font-mono">{{ summary.organization.warna_tema }}</span>
                  </div>
                </div>
              </div>

              <router-link
                :to="`/org/${summary.organization.subdomain}`"
                target="_blank"
                class="mt-4 flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
              >
                <ExternalLinkIcon class="h-3.5 w-3.5" />
                Buka Halaman Publik
              </router-link>
            </div>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'
import { getOrganizationDashboard, type OrganizationDashboardSummary } from '@/services/dashboardService'
import { useAuthStore } from '@/stores/auth'
import {
  FileTextIcon,
  CalendarIcon,
  MegaphoneIcon,
  UsersIcon,
  PlusIcon,
  CalendarClockIcon,
  AlertTriangleIcon,
  HistoryIcon,
  ImageIcon,
  ExternalLinkIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()

const summary = ref<OrganizationDashboardSummary | null>(null)
const isLoading = ref(true)
const errorMessage = ref('')

const loadDashboard = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const data = await getOrganizationDashboard()
    summary.value = data
  } catch (error: any) {
    console.error('Failed to load organization dashboard:', error)
    errorMessage.value = error.response?.data?.message || error.message || 'Gagal memuat ringkasan dashboard organisasi.'
  } finally {
    isLoading.value = false
  }
}

const formatDate = (dateStr?: string) => {
  if (!dateStr) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }).format(new Date(dateStr))
}

const getDay = (dateStr?: string) => {
  if (!dateStr) return '01'
  return new Date(dateStr).getDate().toString().padStart(2, '0')
}

const getMonth = (dateStr?: string) => {
  if (!dateStr) return 'BLN'
  return new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(new Date(dateStr))
}

onMounted(() => {
  loadDashboard()
})
</script>

