<template>
  <AdminLayout>
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white/90">Dashboard</h2>
        <p class="text-sm text-gray-500 mt-1">
          HMPS Teknik Informatika · Periode 2026/2027 · Last synced 2 minutes ago
        </p>
      </div>
      <div class="flex gap-3">
        <button class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
          Export report
        </button>
        <button class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-theme-xs">
          + New content
        </button>
      </div>
    </div>

    <!-- 1. Kartu Metrik Utama -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 xl:grid-cols-4">
      
      <!-- Total Members -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-500">Total Members</span>
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-500 dark:bg-blue-500/20 dark:text-blue-400">
            👥
          </div>
        </div>
        <div class="mt-4">
          <h4 class="text-3xl font-bold text-gray-800 dark:text-white/90">248</h4>
          <div class="mt-2 flex items-center gap-2 text-sm">
            <span class="flex items-center rounded-full bg-success-50 px-2 py-0.5 text-success-600 dark:bg-success-500/10">
              ↗ +12
            </span>
            <span class="text-gray-500">vs last period</span>
          </div>
        </div>
      </div>

      <!-- Active Events -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-500">Active Events</span>
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-500 dark:bg-indigo-500/20 dark:text-indigo-400">
            📅
          </div>
        </div>
        <div class="mt-4">
          <h4 class="text-3xl font-bold text-gray-800 dark:text-white/90">7</h4>
          <div class="mt-2 flex items-center gap-2 text-sm">
            <span class="flex items-center rounded-full bg-success-50 px-2 py-0.5 text-success-600 dark:bg-success-500/10">
              ↗ +2
            </span>
            <span class="text-gray-500">this month</span>
          </div>
        </div>
      </div>

      <!-- Published Articles -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-500">Published Articles</span>
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-50 text-purple-500 dark:bg-purple-500/20 dark:text-purple-400">
            📄
          </div>
        </div>
        <div class="mt-4">
          <h4 class="text-3xl font-bold text-gray-800 dark:text-white/90">132</h4>
          <div class="mt-2 flex items-center gap-2 text-sm">
            <span class="flex items-center rounded-full bg-success-50 px-2 py-0.5 text-success-600 dark:bg-success-500/10">
              ↗ +9
            </span>
            <span class="text-gray-500">last 30 days</span>
          </div>
        </div>
      </div>

      <!-- Visitors -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-500">Visitors</span>
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-500 dark:bg-brand-500/20 dark:text-brand-400">
            👁️
          </div>
        </div>
        <div class="mt-4">
          <h4 class="text-3xl font-bold text-gray-800 dark:text-white/90">18.4k</h4>
          <div class="mt-2 flex items-center gap-2 text-sm">
            <span class="flex items-center rounded-full bg-error-50 px-2 py-0.5 text-error-600 dark:bg-error-500/10">
              ↘ -3.1%
            </span>
            <span class="text-gray-500">vs last month</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Area Visualisasi Data (Grafik) -->
    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
      <!-- Traffic Analytics -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-2">
        <div class="mb-4 flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Traffic analytics</h3>
            <p class="text-sm text-gray-500">Visitors and pageviews over the last 7 months</p>
          </div>
          <span class="rounded-full bg-success-50 px-2.5 py-1 text-xs font-medium text-success-600 dark:bg-success-500/10">+24.8%</span>
        </div>
        <!-- Placeholder Grafik Garis -->
        <div class="h-[280px] w-full rounded-lg bg-gray-50 flex items-center justify-center border border-dashed border-gray-200 dark:bg-gray-800 dark:border-gray-700">
          <span class="text-gray-400 font-medium text-sm">[ Render Line Chart Vue-ApexCharts di sini ]</span>
        </div>
      </div>

      <!-- Content Statistics -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-1">
        <div class="mb-4">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Content statistics</h3>
          <p class="text-sm text-gray-500">Published items by module</p>
        </div>
        <!-- Placeholder Grafik Batang -->
        <div class="h-[280px] w-full rounded-lg bg-gray-50 flex items-center justify-center border border-dashed border-gray-200 dark:bg-gray-800 dark:border-gray-700">
          <span class="text-gray-400 font-medium text-sm">[ Render Bar Chart Vue-ApexCharts di sini ]</span>
        </div>
      </div>
    </div>

    <!-- 3. Area Operasional (Bawah) -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      
      <!-- Recent Activity -->
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-2">
        <h3 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">Recent activity</h3>
        <p class="mb-5 text-sm text-gray-500">Across all modules</p>
        
        <div class="flex flex-col gap-5">
          <div v-for="log in recentActivities" :key="log.id" class="flex items-center justify-between border-b border-gray-100 pb-4 last:border-0 last:pb-0 dark:border-gray-800">
            <div class="flex items-center gap-4">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                {{ log.initial }}
              </div>
              <div>
                <p class="text-sm text-gray-800 dark:text-white/90">
                  <span class="font-semibold">{{ log.user }}</span> {{ log.action }} <span class="font-medium text-gray-900 dark:text-white">{{ log.target }}</span>
                </p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="h-2 w-2 rounded-full" :class="log.indicatorColor"></span>
              <span class="text-xs text-gray-500 whitespace-nowrap">{{ log.time }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Quick Actions & Upcoming Events -->
      <div class="flex flex-col gap-6 xl:col-span-1">
        
        <!-- Quick Actions -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Quick actions</h3>
          <div class="grid grid-cols-2 gap-4">
            <button class="flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
              <span class="text-xl text-brand-500">➕</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">New article</span>
            </button>
            <button class="flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
              <span class="text-xl text-brand-500">👤</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Invite member</span>
            </button>
            <button class="flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
              <span class="text-xl text-brand-500">📤</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Upload media</span>
            </button>
            <button class="flex flex-col items-center justify-center gap-2 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition">
              <span class="text-xl text-brand-500">📢</span>
              <span class="text-xs font-medium text-gray-700 dark:text-gray-300">Post announcement</span>
            </button>
          </div>
        </div>

        <!-- Upcoming Events -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
          <h3 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">Upcoming events</h3>
          <p class="mb-4 text-sm text-gray-500">Next 30 days</p>
          
          <div class="flex flex-col gap-4">
            <div v-for="event in upcomingEvents" :key="event.id" class="flex gap-4">
              <div class="flex flex-col items-center justify-center rounded-lg bg-brand-50 px-3 py-2 dark:bg-brand-500/10 w-14 shrink-0">
                <span class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ event.date }}</span>
                <span class="text-[10px] font-semibold uppercase text-brand-500">{{ event.month }}</span>
              </div>
              <div class="flex flex-col justify-center">
                <h5 class="text-sm font-semibold text-gray-800 dark:text-white/90">{{ event.title }}</h5>
                <p class="text-xs text-gray-500">{{ event.time }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue'
import AdminLayout from '@/components/layout/AdminLayout.vue'

// Mock Data: Recent Activity (Berdasarkan referensi image_83121d.png)
const recentActivities = ref([
  { id: 1, initial: 'NP', user: 'Nadia Prameswari', action: 'published', target: 'Recap: National Hackathon 2026', time: '2h', indicatorColor: 'bg-success-500' },
  { id: 2, initial: 'BW', user: 'Bagas Wicaksono', action: 'submitted for review', target: 'Open Recruitment Staff Muda', time: '5h', indicatorColor: 'bg-brand-500' },
  { id: 3, initial: 'SR', user: 'Salsabila Rania', action: 'uploaded 14 files to', target: 'Gallery / Tech Fair', time: '8h', indicatorColor: 'bg-gray-400' },
  { id: 4, initial: 'YA', user: 'Yoga Ardiansyah', action: 'requested access to', target: 'Documents / LPJ', time: '1d', indicatorColor: 'bg-warning-500' },
  { id: 5, initial: 'DP', user: 'Dimas Prasetyo', action: 'updated', target: 'Organization Structure 2026', time: '2d', indicatorColor: 'bg-gray-400' },
])

// Mock Data: Upcoming Events
const upcomingEvents = ref([
  { id: 1, date: '12', month: 'Aug', title: 'Tech Fair Kampus 2026', time: '09:00 - 17:00' },
  { id: 2, date: '19', month: 'Aug', title: 'Upgrading Pengurus Harian', time: '13:00 - 16:00' },
  { id: 3, date: '27', month: 'Aug', title: 'Webinar Karier Data & AI', time: '19:00 - 21:00' },
])
</script>

