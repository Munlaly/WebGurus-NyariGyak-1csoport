<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const isCollapsed = ref(false);

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

const sidebarWidthClass = computed(() => (isCollapsed.value ? 'w-20' : 'w-70'));

const profileImageSizeClass = computed(() =>
  isCollapsed.value ? 'h-8 w-8' : 'h-12 w-12',
);

const navItemSpacingClass = computed(() =>
  isCollapsed.value ? 'justify-center p-3' : 'gap-3 px-4 py-4',
);

const toggleBtnSpacingClass = computed(() =>
  isCollapsed.value ? 'justify-center' : 'gap-3 px-4',
);

const toggleBtnArrowType = computed(() =>
  isCollapsed.value
    ? 'keyboard_double_arrow_right'
    : 'keyboard_double_arrow_left',
);

const mainContentMarginClass = computed(() =>
  isCollapsed.value ? 'md:ml-20' : 'md:ml-70',
);

const navigation = [
  { name: "Today's Plans", icon: 'calendar_today', href: '/dashboard' },
  { name: 'Weekly Planner', icon: 'event_note', href: '#' },
  { name: 'My Inventory', icon: 'inventory_2', href: '#' },
  { name: 'Shopping List', icon: 'shopping_cart', href: '#' },
  { name: 'Recipes', icon: 'restaurant_menu', href: '#' },
  { name: 'Settings/Goals', icon: 'settings', href: '/settings/targets' },
];
</script>

<template>
  <div
    class="bg-background text-on-background font-body-md flex min-h-screen w-full overflow-x-hidden antialiased"
  >
    <!-- Sidebar -->
    <nav
      :class="[
        'bg-surface dark:bg-surface-dim fixed top-0 left-0 z-40 hidden h-full flex-col shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-300 ease-in-out md:flex',
        sidebarWidthClass,
      ]"
    >
      <div class="flex h-full flex-col gap-2 p-6">
        <!-- Brand / Profile Header -->
        <div
          class="mb-8 flex items-center gap-4"
          :class="{ 'justify-center': isCollapsed }"
        >
          <img
            alt="User profile"
            :class="[
              'border-outline-variant object-cover, h-12 w-12 shrink-0 rounded-full border',
              profileImageSizeClass,
            ]"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDnFlfN9gc-pOKnjod68ZfAFVYgHKchS-RM2cagTzDHWUM1DBLrBcoB1xR-tsZNbd7KH4DI7QzTDM7n_mhOhEpRqukq5UBUaJuQjrDCCOgE0JmCZ6b49UZru_uNr5ruZ83FIMwFfwNwU8qXV1GPyJoDDeHmHnfKEdX6GFgJM73NrUNt3VzfnRv2gJtaQC7hPZnckJ_TLVjXFJStmeL5TSZkPxp-NKYeTOkieIM3soJjQXGtIeBudP8V"
          />
          <div
            v-show="!isCollapsed"
            class="whitespace-nowrap transition-opacity duration-300"
          >
            <h2
              class="font-headline-md text-headline-md text-primary dark:text-primary-fixed font-bold"
            >
              Smart &amp; ZeroWaste
            </h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant">
              Organized Naturalist
            </p>
          </div>
        </div>

        <!-- Navigation Links -->
        <ul class="flex-1 space-y-2">
          <li v-for="item in navigation" :key="item.name">
            <Link
              :href="item.href"
              class="text-on-surface-variant hover:bg-surface-container-low flex items-center rounded-lg transition-all duration-200 hover:scale-[1.02] active:scale-95"
              :class="navItemSpacingClass"
              :title="isCollapsed ? item.name : ''"
            >
              <span class="material-symbols-outlined shrink-0">{{
                item.icon
              }}</span>
              <span
                v-show="!isCollapsed"
                class="font-body-md text-body-md whitespace-nowrap"
                >{{ item.name }}</span
              >
            </Link>
          </li>
        </ul>

        <!-- Collapse Toggle Button at the bottom -->
        <div class="border-outline-variant mt-auto border-t pt-4">
          <button
            class="text-on-surface-variant hover:bg-surface-container-low hover:text-primary flex w-full items-center rounded-lg p-3 transition-colors"
            :class="toggleBtnSpacingClass"
            @click="toggleSidebar"
          >
            <span class="material-symbols-outlined">
              {{ toggleBtnArrowType }}
            </span>
            <span v-show="!isCollapsed" class="font-body-md">Collapse</span>
          </button>
        </div>
      </div>
    </nav>

    <!-- Main Content Area -->
    <div
      :class="[
        'flex min-h-screen flex-1 flex-col transition-all duration-300 ease-in-out',
        mainContentMarginClass,
      ]"
    >
      <!-- TopAppBar -->
      <header
        class="bg-background/80 border-surface-container sticky top-0 z-30 flex h-16 w-full items-center justify-end border-b px-8 backdrop-blur-md"
      >
        <div class="flex items-center gap-6">
          <div
            class="bg-tertiary-fixed text-primary font-label-md text-label-md hidden items-center gap-2 rounded-full px-3 py-1.5 sm:flex"
          >
            <span>🌱 2.4 kg Food Saved</span>
          </div>
          <div
            class="bg-surface-container text-on-surface font-label-md text-label-md hidden items-center gap-2 rounded-full px-3 py-1.5 sm:flex"
          >
            <span>🔥 2100 / 2500 kcal</span>
          </div>

          <!-- Logout Form -->
          <Link
            href="/logout"
            method="post"
            as="button"
            class="font-label-md text-label-md text-on-surface-variant hover:text-primary transition-all hover:scale-[1.02] active:scale-98"
          >
            Log out
          </Link>
        </div>
      </header>

      <!-- Content Canvas (Injected via Slot) -->
      <main class="w-full flex-1 p-6 md:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped></style>
