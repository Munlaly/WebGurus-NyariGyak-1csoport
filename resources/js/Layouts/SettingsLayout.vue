<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from './AuthenticatedLayout.vue';

const props = defineProps<{
  activeTab:
    'security' | 'biometrics' | 'targets' | 'rules' | 'logistics' | 'system';
}>();

const baseTabs = [
  {
    name: 'Account & Security',
    value: 'security',
    routeName: 'settings.security',
    icon: 'i-heroicons-shield-check',
  },
  {
    name: 'Metabolism & Biometrics',
    value: 'biometrics',
    routeName: 'settings.biometrics',
    icon: 'i-heroicons-heart',
  },
  {
    name: 'Targets & Goals',
    value: 'targets',
    routeName: 'settings.targets',
    icon: 'i-heroicons-flag',
  },
  {
    name: 'Dietary Rules',
    value: 'rules',
    routeName: 'settings.rules',
    icon: 'i-heroicons-no-symbol',
  },
  {
    name: 'Kitchen Logistics',
    value: 'logistics',
    routeName: 'settings.logistics',
    icon: 'i-heroicons-home',
  },
  {
    name: 'System',
    value: 'system',
    routeName: 'settings.system',
    icon: 'i-heroicons-cog-6-tooth',
  },
];

// Desktop Tab Data
const navigationTabs = computed(() => {
  return baseTabs.map((tab) => {
    const isActive = props.activeTab === tab.value;

    return {
      ...tab,
      url: route(tab.routeName),
      linkClass: isActive
        ? 'border-primary text-primary'
        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300',
      iconClass: isActive
        ? 'text-primary'
        : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400',
    };
  });
});

// Mobile Dropdown Data & Routing Logic
const mobileDropdownItems = baseTabs.map((tab) => ({
  label: tab.name,
  value: tab.value,
  url: route(tab.routeName),
}));

const currentMobileTab = computed({
  get: () => props.activeTab,
  set: (newValue) => {
    if (newValue !== props.activeTab) {
      const target = mobileDropdownItems.find((t) => t.value === newValue);
      if (target) {
        router.get(target.url);
      }
    }
  },
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex h-full flex-1 flex-col gap-6 md:gap-8">
      <header>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Preferences
        </h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Manage your account, body metrics, dietary rules, and app settings.
        </p>
      </header>

      <!-- Mobile Nav: Routing Dropdown -->
      <div
        class="block border-b border-gray-200 pb-6 md:hidden dark:border-gray-800"
      >
        <USelect
          v-model="currentMobileTab"
          :items="mobileDropdownItems"
          size="lg"
          class="w-full"
        />
      </div>

      <!-- Desktop Nav: Traditional Tabs -->
      <div
        class="hidden border-b border-gray-200 md:block dark:border-gray-800"
      >
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <Link
            v-for="tab in navigationTabs"
            :key="tab.value"
            :href="tab.url"
            class="group inline-flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium whitespace-nowrap transition-colors"
            :class="tab.linkClass"
          >
            <UIcon
              :name="tab.icon"
              class="shrink-0 text-lg"
              :class="tab.iconClass"
            />
            {{ tab.name }}
          </Link>
        </nav>
      </div>

      <main>
        <slot />
      </main>
    </div>
  </AuthenticatedLayout>
</template>
