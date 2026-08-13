<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from './AuthenticatedLayout.vue';
import { computed } from 'vue';

const props = defineProps<{
  activeTab: 'targets' | 'rules' | 'system';
}>();
const tabs = [
  { name: 'Targets', value: 'targets', href: '/settings/targets' },
  { name: 'Rules', value: 'rules', href: '/settings/rules' },
  { name: 'System', value: 'system', href: '/settings/system' },
];
const activeIndex = computed(() =>
  tabs.findIndex((tab) => tab.value === props.activeTab),
);

const leftChevronClasses = computed(() => {
  return activeIndex.value <= 0
    ? 'text-outline-variant cursor-not-allowed opacity-30'
    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary transition-colors';
});

const rightChevronClasses = computed(() => {
  return activeIndex.value >= tabs.length - 1
    ? 'text-outline-variant cursor-not-allowed opacity-30'
    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary transition-colors';
});

const goPrevTab = () => {
  if (activeIndex.value > 0) {
    router.get(tabs[activeIndex.value - 1].href);
  }
};

const goNextTab = () => {
  if (activeIndex.value < tabs.length - 1) {
    router.get(tabs[activeIndex.value + 1].href);
  }
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="mx-auto flex w-full max-w-2xl flex-col gap-8">
      <!-- Shared Page Header -->
      <header class="mb-4">
        <h2 class="font-headline-lg text-headline-lg text-on-background">
          Settings &amp; Goals
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">
          Customize your smart planning experience.
        </p>
      </header>

      <!-- Shared Category Picker -->
      <div
        class="bg-surface-container-lowest flex w-full items-center justify-between rounded-xl p-4 shadow-[0px_4px_20px_rgba(0,0,0,0.04)]"
      >
        <button
          :class="leftChevronClasses"
          :disabled="activeIndex <= 0"
          @click="goPrevTab"
        >
          <span class="material-symbols-outlined">chevron_left</span>
        </button>

        <div
          class="flex flex-1 items-center justify-center gap-8 overflow-x-auto px-4"
        >
          <Link
            v-for="tab in tabs"
            :key="tab.value"
            :href="tab.href"
            :class="[
              'font-body-md text-body-md pb-1 whitespace-nowrap transition-colors',
              activeTab === tab.value
                ? 'text-primary border-primary border-b-2 font-bold'
                : 'text-on-surface-variant/50 hover:text-on-surface-variant',
            ]"
          >
            {{ tab.name }}
          </Link>
        </div>

        <button
          :class="rightChevronClasses"
          :disabled="activeIndex >= tabs.length - 1"
          @click="goNextTab"
        >
          <span class="material-symbols-outlined">chevron_right</span>
        </button>
      </div>

      <!-- Specific Form Content Injected Here -->
      <main>
        <slot />
      </main>
    </div>
  </AuthenticatedLayout>
</template>
