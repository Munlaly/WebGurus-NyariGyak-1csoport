<script setup lang="ts">
import { ref, computed, watchEffect } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

interface MacroTarget {
  current: number;
  target: number;
}

const page = usePage();
watchEffect(() => {
  const userTheme = (page.props.auth as { theme?: string })?.theme || 'light';

  if (userTheme === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
});

const props = withDefaults(
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

const isCollapsed = ref(false);

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
};

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

const mealsProgressPercent = computed(() => {
  if (!props.mealsCooked.total) return 0;
  return Math.min(
    100,
    Math.round((props.mealsCooked.current / props.mealsCooked.total) * 100),
  );
});

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
                v-if="!isCollapsed"
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
            <span v-if="!isCollapsed" class="font-body-md font-medium">
              Collapse
            </span>
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
        class="bg-background/80 border-surface-container sticky top-0 z-30 flex h-16 w-full items-center justify-between border-b px-4 backdrop-blur-md md:px-8"
      >
        <!-- Left: Primary Goal Badge & Meals Cooked Progress -->
        <div class="flex items-center gap-3">
          <!-- Primary Goal Badge -->
          <div
            class="bg-primary/10 text-primary hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-semibold sm:flex"
          >
            <span class="material-symbols-outlined text-[20px]">flag</span>
            <span class="capitalize">{{ primaryGoal }}</span>
          </div>

          <!-- Meals Cooked Progress Bar -->
          <div
            class="bg-surface-container hidden items-center gap-2.5 rounded-full px-3.5 py-1.5 text-sm font-medium lg:flex"
          >
            <span class="material-symbols-outlined text-primary text-[20px]"
              >check_circle</span
            >
            <span>{{ mealsCooked.current }}/{{ mealsCooked.total }} Meals</span>
            <div
              class="bg-outline-variant/30 h-2 w-20 overflow-hidden rounded-full"
            >
              <div
                class="bg-primary h-full rounded-full transition-all duration-300"
                :style="{ width: `${mealsProgressPercent}%` }"
              />
            </div>
          </div>
        </div>

        <!-- Right: Macros (Calories, Protein, Carbs, Fat) & Logout -->
        <div class="flex items-center gap-2 md:gap-3">
          <!-- Calories -->
          <div
            class="bg-surface-container text-on-surface flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-semibold"
          >
            <span class="material-symbols-outlined text-primary text-[20px]"
              >local_fire_department</span
            >
            <span>{{ calories.current }} / {{ calories.target }} kcal</span>
          </div>

          <!-- Protein -->
          <div
            class="bg-surface-container text-on-surface-variant hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium sm:flex"
          >
            <span class="text-primary font-bold">P:</span>
            <span>{{ protein.current }}/{{ protein.target }}g</span>
          </div>

          <!-- Carbs -->
          <div
            class="bg-surface-container text-on-surface-variant hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium xl:flex"
          >
            <span class="text-primary font-bold">C:</span>
            <span>{{ carbs.current }}/{{ carbs.target }}g</span>
          </div>

          <!-- Fat -->
          <div
            class="bg-surface-container text-on-surface-variant hidden items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-medium xl:flex"
          >
            <span class="text-primary font-bold">F:</span>
            <span>{{ fat.current }}/{{ fat.target }}g</span>
          </div>

          <!-- Logout Button -->
          <Link
            href="/logout"
            method="post"
            as="button"
            class="text-on-surface-variant hover:text-primary ml-2 flex items-center gap-1.5 px-2 py-1.5 text-sm font-semibold transition-colors"
          >
            <span class="material-symbols-outlined text-[20px]">logout</span>
            <span class="hidden sm:inline">Log out</span>
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
