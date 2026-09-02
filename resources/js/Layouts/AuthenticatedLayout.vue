<script setup lang="ts">
import { ref, computed, watch, onUnmounted, watchEffect, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useDismissedAlerts } from '../Composables/useDismissedAlerts';

interface MacroTarget {
  current: number;
  target: number;
}

interface InventoryItem {
  id: number;
  expiration_date: string;
}

interface CustomPageProps {
  auth: {
    theme?: string;
    inAppAlerts?: boolean;
    expiringCount?: number;
  };
  expiringAlerts?: {
    expired?: InventoryItem[];
    critical?: InventoryItem[];
    urgent?: InventoryItem[];
  };
  flash?: {
    success?: string;
  };
}

withDefaults(
  defineProps<{
    primaryGoal?: string;
    mealsCooked?: {
      current: number;
      total: number;
    };
    calories?: MacroTarget;
    protein?: MacroTarget;
    carbs?: MacroTarget;
    fat?: MacroTarget;
  }>(),
  {
    primaryGoal: 'General Health',
    mealsCooked: () => ({ current: 0, total: 3 }),
    calories: () => ({ current: 0, target: 2000 }),
    protein: () => ({ current: 0, target: 140 }),
    carbs: () => ({ current: 0, target: 220 }),
    fat: () => ({ current: 0, target: 65 }),
  },
);

const page = usePage();
const { dismissedIds } = useDismissedAlerts();

const navigation = [
  { name: "Today's Plans", icon: 'calendar_today', href: '/' },
  { name: 'Weekly Planner', icon: 'event_note', href: '/meal-plan' },
  { name: 'My Inventory', icon: 'inventory_2', href: '/inventory' },
  { name: 'Shopping List', icon: 'shopping_cart', href: '/shopping-list' },
  { name: 'Recipes', icon: 'restaurant_menu', href: '#' },
  { name: 'Alerts', icon: 'notifications', href: '/alerts' },
  { name: 'Settings/Goals', icon: 'settings', href: '/settings/targets' },
];

const isCollapsed = ref(false);
const isMobileMenuOpen = ref(false);
const showTopAlert = ref(false);
const showFlashToast = ref(false);

const typedPageProps = computed(() => page.props as unknown as CustomPageProps);
const sidebarWidthClass = computed(() => (isCollapsed.value ? 'w-20' : 'w-72'));
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
const headerPositionClass = computed(() =>
  isCollapsed.value ? 'left-0 md:left-20' : 'left-0 md:left-72',
);
const mobileMenuTransformClass = computed(() =>
  isMobileMenuOpen.value ? 'translate-x-0' : 'translate-x-full',
);
const expiringCount = computed(
  () => typedPageProps.value.auth?.expiringCount || 0,
);
const flashMessage = computed(() => typedPageProps.value.flash?.success);

const availableAlertsCount = computed(() => {
  const alerts = typedPageProps.value.expiringAlerts || {
    expired: [],
    critical: [],
    urgent: [],
  };

  const activeExpired = (alerts.expired || []).filter(
    (item) => !dismissedIds.value.includes(item.id),
  );

  const critical = alerts.critical || [];
  const urgent = alerts.urgent || [];

  return activeExpired.length + critical.length + urgent.length;
});

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value;
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function handleLogout() {
  sessionStorage.clear();
  router.post(route('logout'));
}

function closeFlashToast() {
  showFlashToast.value = false;
}

watchEffect(() => {
  const userTheme = typedPageProps.value.auth?.theme || 'light';

  if (userTheme === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
});

watch(isMobileMenuOpen, (isOpen) => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = isOpen ? 'hidden' : '';
  }
});

watch(flashMessage, (newMessage) => {
  if (newMessage) {
    showFlashToast.value = true;
    setTimeout(() => {
      showFlashToast.value = false;
    }, 4500);
  }
});

onMounted(() => {
  const inAppAlertsEnabled = typedPageProps.value.auth?.inAppAlerts ?? true;

  if (inAppAlertsEnabled && expiringCount.value > 0) {
    if (!sessionStorage.getItem('top_alert_seen')) {
      setTimeout(() => {
        showTopAlert.value = true;
      }, 800);
    }
  }
});

onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = '';
  }
});
onUnmounted(() => {
  if (typeof document !== 'undefined') {
    document.body.style.overflow = '';
  }
});
</script>

<template>
  <UApp>
    <div
      class="bg-background text-on-background font-body-md relative flex min-h-screen w-full overflow-x-hidden antialiased"
    >
      <!-- TOP CENTER NOTIFICATION -->
      <Transition
        enter-active-class="transition ease-out duration-300 transform"
        enter-from-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showFlashToast"
          class="fixed top-6 left-1/2 z-100 w-11/12 max-w-md -translate-x-1/2 sm:w-full"
        >
          <div
            class="pointer-events-auto flex items-center justify-between gap-4 rounded-2xl border border-emerald-200 bg-white p-4 shadow-2xl ring-1 ring-black/5 dark:border-emerald-900/50 dark:bg-gray-900"
          >
            <div class="flex items-center gap-4">
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30"
              >
                <span
                  class="material-symbols-outlined text-emerald-600 dark:text-emerald-400"
                  >check_circle</span
                >
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">
                  Inventory Updated
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ flashMessage }}
                </p>
              </div>
            </div>
            <button
              class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
              @click="closeFlashToast"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>
        </div>
      </Transition>

      <!-- DESKTOP SIDEBAR -->
      <nav
        :class="[
          'bg-surface dark:bg-surface-dim fixed top-0 left-0 z-40 hidden h-full flex-col shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-300 ease-in-out md:flex',
          sidebarWidthClass,
        ]"
      >
        <div class="flex h-full flex-col p-4">
          <!-- Brand / Profile Header -->
          <div
            class="mb-6 flex items-center transition-all duration-300"
            :class="
              isCollapsed ? 'justify-center gap-0 px-0' : 'gap-3.5 px-2 py-2'
            "
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
                Smart Meal Plan
              </h2>
              <p
                class="font-body-sm text-body-sm text-on-surface-variant truncate"
              >
                Personal Nutrition
              </p>
            </div>
          </div>

          <!-- Desktop Navigation Links -->
          <ul class="flex-1 space-y-1.5">
            <li v-for="item in navigation" :key="item.name">
              <Link
                :href="item.href"
                class="text-on-surface-variant hover:bg-surface-container-low active:bg-surface-container-low relative flex items-center justify-between rounded-xl transition-all duration-200 active:scale-95"
                :class="navItemSpacingClass"
                :title="isCollapsed ? item.name : ''"
              >
                <div class="flex items-center gap-3">
                  <span
                    class="material-symbols-outlined shrink-0 text-[22px]"
                    >{{ item.icon }}</span
                  >
                  <span
                    v-if="!isCollapsed"
                    class="font-body-md text-body-md font-medium whitespace-nowrap"
                  >
                    {{ item.name }}
                  </span>
                </div>

                <!-- Live Counter Badge -->
                <span
                  v-if="
                    item.name === 'Alerts' &&
                    availableAlertsCount > 0 &&
                    !isCollapsed
                  "
                  class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-600 dark:bg-red-900/40 dark:text-red-400"
                >
                  {{ availableAlertsCount }}
                </span>

                <!-- Red dot indicator when sidebar is collapsed -->
                <span
                  v-if="
                    item.name === 'Alerts' &&
                    availableAlertsCount > 0 &&
                    isCollapsed
                  "
                  class="absolute top-2.5 right-2.5 h-2.5 w-2.5 rounded-full bg-red-600 ring-2 ring-white dark:ring-gray-900"
                ></span>
              </Link>
            </li>
          </ul>

          <!-- Collapse Toggle Button -->
          <div class="border-outline-variant mt-auto border-t pt-3">
            <button
              class="text-on-surface-variant hover:bg-surface-container-low hover:text-primary flex w-full items-center rounded-xl transition-colors active:scale-95"
              :class="toggleBtnSpacingClass"
              @click="toggleSidebar"
            >
              <span class="material-symbols-outlined shrink-0">{{
                toggleBtnArrowType
              }}</span>
              <span v-if="!isCollapsed" class="font-body-md font-medium"
                >Collapse</span
              >
            </button>
          </div>
        </div>
      </nav>

      <!-- MAIN CONTENT AREA -->
      <div
        :class="[
          'flex min-h-screen flex-1 flex-col transition-all duration-300 ease-in-out',
          mainContentMarginClass,
        ]"
      >
        <!-- TOP APP BAR -->
        <header
          :class="[
            'border-surface-container bg-surface fixed top-0 right-0 z-50 flex h-16 items-center justify-between border-b px-3 transition-all duration-300 ease-in-out md:px-8',
            headerPositionClass,
          ]"
        >
          <!-- Left: Cooked Meals -->
          <div class="flex items-center gap-2">
            <div
              class="bg-surface-container text-on-surface flex items-center gap-1.5 rounded-full px-2.5 py-1.5 text-sm font-semibold"
            >
              <span class="material-symbols-outlined text-primary text-[18px]"
                >check_circle</span
              >
              <span class="whitespace-nowrap"
                >{{ mealsCooked.current ?? 0 }}/{{ mealsCooked.total ?? 3 }}
                <span class="hidden sm:inline">Meals</span></span
              >
            </div>
          </div>

          <!-- Right: Macros & Logout -->
          <div class="flex items-center gap-1.5 md:gap-3">
            <!-- Calories: Visible everywhere -->
            <div
              class="bg-surface-container text-on-surface flex items-center gap-1.5 rounded-full px-2.5 py-1.5 text-sm font-semibold"
            >
              <span class="material-symbols-outlined text-primary text-[18px]"
                >local_fire_department</span
              >
              <span class="whitespace-nowrap"
                >{{ calories.current ?? 0 }}
                <span class="hidden sm:inline"
                  >/ {{ calories.target ?? 0 }} kcal</span
                ></span
              >
            </div>

            <!-- P/C/F Macros: Hidden on Mobile, Visible on md+ -->
            <div
              class="bg-surface-container text-on-surface-variant hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium md:flex"
            >
              <span class="text-primary font-bold">P:</span>
              <span>{{ protein.current ?? 0 }}g</span>
            </div>
            <div
              class="bg-surface-container text-on-surface-variant hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium lg:flex"
            >
              <span class="text-primary font-bold">C:</span>
              <span>{{ carbs.current ?? 0 }}g</span>
            </div>
            <div
              class="bg-surface-container text-on-surface-variant hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium lg:flex"
            >
              <span class="text-primary font-bold">F:</span>
              <span>{{ fat.current ?? 0 }}g</span>
            </div>

            <!-- Logout Button (Using Bence's handleLogout) -->
            <button
              class="text-on-surface-variant hover:text-error active:bg-error/10 flex items-center justify-center rounded-full p-2 transition-colors active:scale-95 md:px-3 md:py-1.5"
              title="Log out"
              @click="handleLogout"
            >
              <span class="material-symbols-outlined text-[20px] md:text-[18px]"
                >logout</span
              >
              <span
                class="hidden md:ml-1.5 md:inline md:text-sm md:font-semibold"
                >Log out</span
              >
            </button>
          </div>
        </header>

        <!-- Content Canvas Wrapper  -->
        <div class="mt-16 flex w-full flex-1 flex-col overflow-hidden">
          <main class="flex w-full flex-1 flex-col p-4 pb-24 md:p-8 md:pb-8">
            <slot />
          </main>
        </div>
      </div>

      <!-- NATIVE SLIDEOVER MENU-->
      <div
        :class="[
          'fixed inset-0 top-16 z-40 flex flex-col overflow-y-auto overscroll-contain bg-white p-6 pb-28 shadow-2xl transition-transform duration-300 ease-in-out md:hidden dark:bg-gray-950',
          mobileMenuTransformClass,
        ]"
      >
        <!-- Header (Profile) -->
        <div
          class="mb-8 flex items-center gap-4 border-b border-gray-200 pb-6 dark:border-gray-800"
        >
          <img
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDnFlfN9gc-pOKnjod68ZfAFVYgHKchS-RM2cagTzDHWUM1DBLrBcoB1xR-tsZNbd7KH4DI7QzTDM7n_mhOhEpRqukq5UBUaJuQjrDCCOgE0JmCZ6b49UZru_uNr5ruZ83FIMwFfwNwU8qXV1GPyJoDDeHmHnfKEdX6GFgJM73NrUNt3VzfnRv2gJtaQC7hPZnckJ_TLVjXFJStmeL5TSZkPxp-NKYeTOkieIM3soJjQXGtIeBudP8V"
            class="h-14 w-14 rounded-full border border-gray-200 object-cover shadow-sm"
          />
          <div class="flex flex-col">
            <span
              class="text-lg font-bold tracking-tight text-gray-900 dark:text-gray-100"
              >Smart Meal Plan</span
            >
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400"
              >Personal Nutrition</span
            >
          </div>
        </div>

        <!-- Full Navigation List -->
        <div class="flex flex-1 flex-col justify-between gap-2.5">
          <Link
            v-for="item in navigation"
            :key="item.name"
            :href="item.href"
            class="flex items-center gap-4 rounded-2xl bg-gray-50 px-5 py-4 font-semibold text-gray-900 shadow-sm transition-all hover:bg-gray-100 active:scale-[0.98] dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800"
            @click="isMobileMenuOpen = false"
          >
            <span
              class="material-symbols-outlined text-[26px] text-green-600 dark:text-green-500"
              >{{ item.icon }}</span
            >
            <span class="text-base tracking-wide">{{ item.name }}</span>
          </Link>
        </div>
      </div>

      <!-- MOBILE BOTTOM NAVIGATION -->
      <nav
        class="fixed bottom-0 left-0 z-50 flex w-full justify-around border-t border-gray-200 bg-white px-2 py-3 pb-[max(env(safe-area-inset-bottom),1rem)] shadow-[0_-4px_24px_rgba(0,0,0,0.12)] md:hidden dark:border-gray-800 dark:bg-gray-950"
      >
        <!-- Today's Plans -->
        <Link
          href="/dashboard"
          class="flex flex-col items-center gap-1.5 text-gray-500 transition-all hover:text-green-600 active:scale-95 active:text-green-600"
          @click="isMobileMenuOpen = false"
        >
          <span class="material-symbols-outlined text-[26px]"
            >calendar_today</span
          >
          <span class="text-[11px] font-bold">Today</span>
        </Link>

        <!-- Weekly Planner -->
        <Link
          href="/meal-plan"
          class="flex flex-col items-center gap-1.5 text-gray-500 transition-all hover:text-green-600 active:scale-95 active:text-green-600"
          @click="isMobileMenuOpen = false"
        >
          <span class="material-symbols-outlined text-[26px]">event_note</span>
          <span class="text-[11px] font-bold">Weekly</span>
        </Link>

        <!-- More / Burger Menu Trigger -->
        <button
          :class="[
            'flex flex-col items-center gap-1.5 transition-all active:scale-95',
            isMobileMenuOpen
              ? 'text-green-600'
              : 'text-gray-500 hover:text-green-600',
          ]"
          @click="toggleMobileMenu"
        >
          <span class="material-symbols-outlined text-[26px]">{{
            isMobileMenuOpen ? 'close' : 'menu'
          }}</span>
          <span class="text-[11px] font-bold">{{
            isMobileMenuOpen ? 'Close' : 'More'
          }}</span>
        </button>
      </nav>
    </div>
  </UApp>
</template>
