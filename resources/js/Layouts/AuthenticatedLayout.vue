<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const isCollapsed = ref(false);

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

const sidebarWidthClass = computed(() => (isCollapsed.value ? 'w-20' : 'w-72'));

const profileImageSizeClass = computed(() =>
  isCollapsed.value ? 'h-9 w-9' : 'h-11 w-11',
);

const navItemSpacingClass = computed(() =>
  isCollapsed.value ? 'justify-center p-3' : 'gap-3 px-4 py-3.5',
);

const toggleBtnSpacingClass = computed(() =>
  isCollapsed.value ? 'justify-center p-3' : 'gap-3 px-4 py-3',
);

const toggleBtnArrowType = computed(() =>
  isCollapsed.value
    ? 'keyboard_double_arrow_right'
    : 'keyboard_double_arrow_left',
);

const mainContentMarginClass = computed(() =>
  isCollapsed.value ? 'md:ml-20' : 'md:ml-72',
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
      <div class="flex h-full flex-col p-4">
        <!-- Brand / Profile Header (Fixed Circle & Sizing) -->
        <div
          class="mb-6 flex items-center gap-3.5 px-2 py-2"
          :class="{ 'justify-center px-0': isCollapsed }"
        >
          <img
            alt="User profile"
            :class="[
              'border-outline-variant aspect-square shrink-0 rounded-full border object-cover transition-all duration-300',
              isCollapsed ? 'h-9 w-9 min-w-9' : 'h-11 w-11 min-w-11',
            ]"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDnFlfN9gc-pOKnjod68ZfAFVYgHKchS-RM2cagTzDHWUM1DBLrBcoB1xR-tsZNbd7KH4DI7QzTDM7n_mhOhEpRqukq5UBUaJuQjrDCCOgE0JmCZ6b49UZru_uNr5ruZ83FIMwFfwNwU8qXV1GPyJoDDeHmHnfKEdX6GFgJM73NrUNt3VzfnRv2gJtaQC7hPZnckJ_TLVjXFJStmeL5TSZkPxp-NKYeTOkieIM3soJjQXGtIeBudP8V"
          />
          <div
            v-if="!isCollapsed"
            class="min-w-0 flex-1 overflow-hidden whitespace-nowrap"
          >
            <h2
              class="font-headline-md text-headline-md text-primary dark:text-primary-fixed truncate font-bold"
            >
              Smart &amp; ZeroWaste
            </h2>
            <p
              class="font-body-sm text-body-sm text-on-surface-variant truncate"
            >
              Organized Naturalist
            </p>
          </div>
        </div>

        <!-- Navigation Links -->
        <ul class="flex-1 space-y-1.5">
          <li v-for="item in navigation" :key="item.name">
            <Link
              :href="item.href"
              class="text-on-surface-variant hover:bg-surface-container-low flex items-center rounded-xl transition-all duration-200 hover:scale-[1.02] active:scale-95"
              :class="navItemSpacingClass"
              :title="isCollapsed ? item.name : ''"
            >
              <span class="material-symbols-outlined shrink-0 text-[22px]">{{
                item.icon
              }}</span>
              <span
                v-show="!isCollapsed"
                class="font-body-md text-body-md font-medium whitespace-nowrap"
                >{{ item.name }}</span
              >
            </Link>
          </li>
        </ul>

        <!-- Collapse Toggle Button -->
        <div class="border-outline-variant mt-auto border-t pt-3">
          <button
            class="text-on-surface-variant hover:bg-surface-container-low hover:text-primary flex w-full items-center rounded-xl transition-colors"
            :class="toggleBtnSpacingClass"
            @click="toggleSidebar"
          >
            <span class="material-symbols-outlined shrink-0">
              {{ toggleBtnArrowType }}
            </span>
            <span v-show="!isCollapsed" class="font-body-md font-medium"
              >Collapse</span
            >
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
      <!-- TopAppBar (Emoji-Free Metric Badges) -->
      <header
        class="bg-background/80 border-surface-container sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b px-6 backdrop-blur-md md:px-8"
      >
        <!-- Optional Left breadcrumb/status on top bar -->
        <div
          class="text-on-surface-variant hidden items-center gap-2 text-sm md:flex"
        >
          <span class="material-symbols-outlined text-[18px]">eco</span>
          <span>Zero Waste Plan Active</span>
        </div>

        <div class="flex items-center gap-4">
          <!-- Food Saved Pill -->
          <div
            class="bg-tertiary-fixed text-primary font-label-md text-label-md flex items-center gap-1.5 rounded-full px-3.5 py-1.5 font-medium"
          >
            <span class="material-symbols-outlined text-[16px]">compost</span>
            <span>2.4 kg Saved</span>
          </div>

          <!-- Calories Target Pill -->
          <div
            class="bg-surface-container text-on-surface font-label-md text-label-md flex items-center gap-1.5 rounded-full px-3.5 py-1.5 font-medium"
          >
            <span class="material-symbols-outlined text-[16px]"
              >local_fire_department</span
            >
            <span>2100 / 2500 kcal</span>
          </div>

          <!-- Logout Button -->
          <Link
            href="/logout"
            method="post"
            as="button"
            class="font-label-md text-label-md text-on-surface-variant hover:text-primary ml-2 flex items-center gap-1 transition-colors"
          >
            <span class="material-symbols-outlined text-[18px]">logout</span>
            <span>Log out</span>
          </Link>
        </div>
      </header>

      <!-- Content Canvas -->
      <main class="flex w-full flex-1 flex-col p-6 md:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
