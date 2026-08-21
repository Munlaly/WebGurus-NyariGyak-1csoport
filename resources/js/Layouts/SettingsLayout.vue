<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import AuthenticatedLayout from './AuthenticatedLayout.vue';

const props = defineProps<{
  activeTab: 'targets' | 'rules' | 'system';
}>();

const baseTabs = [
  {
    name: 'Targets',
    value: 'targets',
    routeName: 'settings.targets',
    icon: 'i-heroicons-flag',
  },
  {
    name: 'Rules',
    value: 'rules',
    routeName: 'settings.rules',
    icon: 'i-heroicons-clipboard-document-check',
  },
  {
    name: 'System',
    value: 'system',
    routeName: 'settings.system',
    icon: 'i-heroicons-cog-6-tooth',
  },
];

const navigationTabs = computed(() => {
  return baseTabs.map((tab) => {
    const isActive = props.activeTab === tab.value;

    return {
      ...tab,
      url: route(tab.routeName),
      linkClass: isActive
        ? 'border-primary text-primary dark:text-primary-400'
        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300',
      iconClass: isActive
        ? 'text-primary dark:text-primary-400'
        : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400',
    };
  });
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex h-full flex-1 flex-col gap-6 md:gap-8">
      <header>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
          Settings &amp; Goals
        </h1>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
          Customize your smart planning experience and dietary rules.
        </p>
      </header>

      <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <Link
            v-for="tab in navigationTabs"
            :key="tab.value"
            :href="tab.url"
            class="group inline-flex items-center gap-2 border-b-2 px-1 py-4 text-sm font-medium transition-colors"
            :class="tab.linkClass"
          >
            <UIcon :name="tab.icon" class="text-lg" :class="tab.iconClass" />
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
