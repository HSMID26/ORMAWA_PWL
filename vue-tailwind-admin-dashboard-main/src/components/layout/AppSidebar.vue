<template>
  <aside
    :class="[
      'fixed mt-16 flex flex-col lg:mt-0 top-0 px-5 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-99999 border-r border-gray-200',
      {
        'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
        'lg:w-[90px]': !isExpanded && !isHovered,
        'translate-x-0 w-[290px]': isMobileOpen,
        '-translate-x-full': !isMobileOpen,
        'lg:translate-x-0': true,
      },
    ]"
    @mouseenter="!isExpanded && (isHovered = true)"
    @mouseleave="isHovered = false"
  >
    <div
      :class="[
        'py-8 flex',
        !isExpanded && !isHovered ? 'lg:justify-center' : 'justify-start',
      ]"
    >
      <router-link to="/">
        <AppLogo :collapsed="!isExpanded && !isHovered && !isMobileOpen" />
      </router-link>
    </div>
    <div
      class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar"
    >
      <nav class="mb-6">
        <div class="flex flex-col gap-4">
          <div v-for="(menuGroup, groupIndex) in menuGroups" :key="groupIndex">
            <h2
              :class="[
                'mb-4 text-xs uppercase flex leading-[20px] text-gray-400',
                !isExpanded && !isHovered
                  ? 'lg:justify-center'
                  : 'justify-start',
              ]"
            >
              <template v-if="isExpanded || isHovered || isMobileOpen">
                {{ menuGroup.title }}
              </template>
              <MoreHorizontalIcon v-else />
            </h2>
            <ul class="flex flex-col gap-4">
              <li v-for="(item, index) in menuGroup.items" :key="item.name">
                <button
                  v-if="item.subItems"
                  @click="toggleSubmenu(groupIndex, index)"
                  :class="[
                    'menu-item group w-full',
                    {
                      'menu-item-active': isSubmenuOpen(groupIndex, index),
                      'menu-item-inactive': !isSubmenuOpen(groupIndex, index),
                    },
                    !isExpanded && !isHovered
                      ? 'lg:justify-center'
                      : 'lg:justify-start',
                  ]"
                >
                  <span
                    :class="[
                      isSubmenuOpen(groupIndex, index)
                        ? 'menu-item-icon-active'
                        : 'menu-item-icon-inactive',
                    ]"
                  >
                    <component :is="item.icon" />
                  </span>
                  <span
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="menu-item-text"
                    >{{ item.name }}</span
                  >
                  <ChevronDownIcon
                    v-if="isExpanded || isHovered || isMobileOpen"
                    :class="[
                      'ml-auto w-5 h-5 transition-transform duration-200',
                      {
                        'rotate-180 text-brand-500': isSubmenuOpen(
                          groupIndex,
                          index
                        ),
                      },
                    ]"
                  />
                </button>
                <router-link
                  v-else-if="item.path"
                  :to="item.path"
                  :class="[
                    'menu-item group',
                    {
                      'menu-item-active': isActive(item.path),
                      'menu-item-inactive': !isActive(item.path),
                    },
                  ]"
                >
                  <span
                    :class="[
                      isActive(item.path)
                        ? 'menu-item-icon-active'
                        : 'menu-item-icon-inactive',
                    ]"
                  >
                    <component :is="item.icon" />
                  </span>
                  <span
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="menu-item-text"
                    >{{ item.name }}</span
                  >
                </router-link>
                <transition
                  @enter="startTransition"
                  @after-enter="endTransition"
                  @before-leave="startTransition"
                  @after-leave="endTransition"
                >
                  <div
                    v-show="
                      isSubmenuOpen(groupIndex, index) &&
                      (isExpanded || isHovered || isMobileOpen)
                    "
                  >
                    <ul class="mt-2 space-y-1 ml-9">
                      <li v-for="subItem in item.subItems" :key="subItem.name">
                        <router-link
                          :to="subItem.path"
                          :class="[
                            'menu-dropdown-item',
                            {
                              'menu-dropdown-item-active': isActive(
                                subItem.path
                              ),
                              'menu-dropdown-item-inactive': !isActive(
                                subItem.path
                              ),
                            },
                          ]"
                        >
                          {{ subItem.name }}
                          <span class="flex items-center gap-1 ml-auto">
                            <span
                              v-if="subItem.new"
                              :class="[
                                'menu-dropdown-badge',
                                {
                                  'menu-dropdown-badge-active': isActive(
                                    subItem.path
                                  ),
                                  'menu-dropdown-badge-inactive': !isActive(
                                    subItem.path
                                  ),
                                },
                              ]"
                            >
                              new
                            </span>
                            <span
                              v-if="subItem.pro"
                              :class="[
                                'menu-dropdown-badge',
                                {
                                  'menu-dropdown-badge-active': isActive(
                                    subItem.path
                                  ),
                                  'menu-dropdown-badge-inactive': !isActive(
                                    subItem.path
                                  ),
                                },
                              ]"
                            >
                              pro
                            </span>
                          </span>
                        </router-link>
                      </li>
                    </ul>
                  </div>
                </transition>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <SidebarWidget v-if="isExpanded || isHovered || isMobileOpen" />
    </div>
  </aside>
</template>

<script setup lang="ts">
import AppLogo from '@/components/common/AppLogo.vue'
import { ref, computed } from "vue";
import { useAuthStore } from '@/stores/auth';
import { useRoute } from "vue-router";

import {
  LayoutGridIcon,
  CalendarIcon,
  UserCircleIcon,
  MessageSquareIcon,
  MailIcon,
  FileTextIcon,
  PieChartIcon,
  ChevronDownIcon,
  MoreHorizontalIcon,
  FileIcon,
  TableIcon,
  ListIcon,
  PlugIcon,
  BoxIcon,
  SettingsIcon,
  CalendarClockIcon,
  UsersIcon
} from 'lucide-vue-next';
import SidebarWidget from "./SidebarWidget.vue";
import { useSidebar } from "@/composables/useSidebar";

const route = useRoute();

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();

// ─── Types ────────────────────────────────────────────────────────────────────
interface MenuItem {
  icon?: unknown
  name: string
  path?: string
  subItems?: { name: string; path: string; new?: boolean; pro?: boolean }[]
}
interface MenuGroup {
  title: string
  items: MenuItem[]
}

const authStore = useAuthStore()

const menuGroups = computed<MenuGroup[]>(() => {
  const role = authStore.role

  if (role === 'Super Admin') {
    return [
      {
        title: 'Super Admin',
        items: [
          { icon: LayoutGridIcon, name: 'Dashboard', path: '/dashboard' },
        ]
      },
      {
        title: 'ORGANISASI',
        items: [
          { icon: BoxIcon, name: 'Semua Organisasi', path: '/super-admin/organizations' },
          { icon: FileTextIcon, name: 'Pendaftaran Organisasi', path: '/super-admin/organization-registrations' },
          { icon: UsersIcon, name: 'Admin Organisasi', path: '/super-admin/organization-admins' },
          { icon: CalendarClockIcon, name: 'Periode / Renewal', path: '/super-admin/periods' },
        ]
      },
      {
        title: 'MONITORING',
        items: [
          { icon: UserCircleIcon, name: 'Pengguna Global', path: '/super-admin/users' },
          { icon: ListIcon, name: 'Monitoring Konten', path: '/super-admin/content' },
          { icon: FileIcon, name: 'Activity Log', path: '/super-admin/activity-logs' },
        ]
      },
      {
        title: 'SYSTEM',
        items: [
          { icon: SettingsIcon, name: 'Pengaturan Platform', path: '/super-admin/settings' },
        ]
      }
    ]
  }

  if (role === 'Admin Organisasi') {
    return [
      {
        title: 'Utama',
        items: [
          { icon: LayoutGridIcon, name: 'Dashboard', path: '/organization/dashboard' },
        ]
      },
      {
        title: 'Konten',
        items: [
          { icon: FileTextIcon, name: 'Artikel', path: '/organization/posts' },
          { icon: CalendarIcon, name: 'Agenda', path: '/organization/agenda' },
          { icon: MessageSquareIcon, name: 'Pengumuman', path: '/organization/announcements' },
          { icon: PieChartIcon, name: 'Galeri', path: '/organization/gallery' },
          { icon: FileIcon, name: 'Dokumen', path: '/organization/documents' },
        ]
      },
      {
        title: 'Organisasi',
        items: [
          { icon: UsersIcon, name: 'Struktur Organisasi', path: '/organization/committees' },
          { icon: CalendarClockIcon, name: 'Manajemen Periode', path: '/organization/period' },
          { icon: UserCircleIcon, name: 'Pengguna Organisasi', path: '/organization/users' },
          { icon: SettingsIcon, name: 'Pengaturan Organisasi', path: '/organization/settings' },
        ]
      },
      {
        title: 'Audit Trail',
        items: [
          { icon: FileTextIcon, name: 'Activity Log', path: '/organization/activity-logs' },
        ]
      }
    ]
  }

  if (role === 'Editor') {
    return [
      {
        title: 'Utama',
        items: [
          { icon: LayoutGridIcon, name: 'Dashboard', path: '/organization/dashboard' },
        ]
      },
      {
        title: 'Manajemen Konten',
        items: [
          { icon: FileTextIcon, name: 'Artikel', path: '/organization/posts' },
          { icon: CalendarIcon, name: 'Agenda', path: '/organization/agenda' },
          { icon: MessageSquareIcon, name: 'Pengumuman', path: '/organization/announcements' },
          { icon: PieChartIcon, name: 'Galeri', path: '/organization/gallery' },
          { icon: FileIcon, name: 'Dokumen', path: '/organization/documents' },
        ]
      }
    ]
  }

  if (role === 'Kontributor') {
    return [
      {
        title: 'Utama',
        items: [
          { icon: LayoutGridIcon, name: 'Dashboard', path: '/organization/dashboard' },
        ]
      },
      {
        title: 'Artikel',
        items: [
          { icon: FileTextIcon, name: 'Artikel Saya', path: '/organization/posts' },
          { icon: FileIcon, name: 'Tulis Artikel', path: '/organization/posts/create' },
        ]
      }
    ]
  }

  return []
});

const isActive = (path: string) => route.path === path;

const toggleSubmenu = (groupIndex: number, itemIndex: number) => {
  const key = `${groupIndex}-${itemIndex}`;
  openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isAnySubmenuRouteActive = computed(() => {
  return menuGroups.value.some((group) =>
    group.items.some(
      (item) =>
        item.subItems && item.subItems.some((subItem) => isActive(subItem.path))
    )
  );
});

const isSubmenuOpen = (groupIndex: number, itemIndex: number) => {
  const key = `${groupIndex}-${itemIndex}`;
  return (
    openSubmenu.value === key ||
    (isAnySubmenuRouteActive.value &&
      menuGroups.value[groupIndex]?.items[itemIndex]?.subItems?.some((subItem) =>
        isActive(subItem.path)
      ))
  );
};

const startTransition = (el: Element) => {
  const htmlEl = el as HTMLElement;
  htmlEl.style.height = "auto";
  const height = htmlEl.scrollHeight;
  htmlEl.style.height = "0px";
  htmlEl.offsetHeight; // force reflow
  htmlEl.style.height = height + "px";
};

const endTransition = (el: Element) => {
  (el as HTMLElement).style.height = "";
};
</script>
