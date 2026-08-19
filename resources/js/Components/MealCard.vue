<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
  id: number;
  title: string;
  calories: number;
  prepTime: number;
  imageUrl: string;
  imageAlt: string;
  isPrepared: boolean;
}>();

const emit = defineEmits<{
  (e: 'toggle-cooked'): void;
}>();

const btnText = computed(() =>
  props.isPrepared ? 'Cooked (Click to undo)' : 'Mark as Cooked',
);
const buttonClass = computed(() =>
  props.isPrepared
    ? 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high border border-outline-variant'
    : 'bg-primary text-white hover:bg-primary-600 shadow-sm',
);

const imageStateClass = computed(() =>
  props.isPrepared ? 'opacity-50 grayscale-[40%]' : '',
);

const contentStateClass = computed(() =>
  props.isPrepared ? 'opacity-75' : '',
);
</script>

<template>
  <div
    class="bg-surface-container-lowest group flex cursor-pointer flex-col overflow-hidden rounded-2xl shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-300 hover:scale-[1.01] hover:shadow-[0px_12px_32px_rgba(0,0,0,0.08)]"
  >
    <!-- Image Header with 16:9 Aspect Ratio -->
    <Link
      :href="route('recipe.show', id)"
      class="bg-surface-container relative aspect-video w-full overflow-hidden"
    >
      <!-- Floating Cooked Status Badge -->
      <div
        v-if="isPrepared"
        class="bg-surface-container-lowest/90 text-primary font-label-md text-label-md absolute top-3 right-3 z-10 flex items-center gap-1 rounded-full px-3 py-1 shadow-md backdrop-blur-sm"
      >
        <span class="material-symbols-outlined text-[16px]">check_circle</span>
        Cooked
      </div>

      <!-- Hero Image -->
      <img
        :class="[
          'h-full w-full object-cover transition-all duration-500 group-hover:scale-105',
          imageStateClass,
        ]"
        :alt="imageAlt"
        :src="imageUrl"
      />
    </Link>

    <!-- Content Body -->
    <div :class="['flex flex-1 flex-col p-6', contentStateClass]">
      <!-- Prominent Title with Consistent Height -->
      <h3
        class="text-on-surface mb-4 line-clamp-2 min-h-[3.5rem] text-xl leading-snug font-bold tracking-tight md:text-2xl"
      >
        {{ title }}
      </h3>

      <!-- Structured Macro / Meta Pills -->
      <div class="mb-6 flex flex-wrap items-center gap-2">
        <div
          class="bg-primary-50 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300 flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold"
        >
          <span class="material-symbols-outlined text-[18px]"
            >local_fire_department</span
          >
          <span>{{ calories }} kcal</span>
        </div>

        <div
          class="bg-surface-container-low text-on-surface-variant flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium"
        >
          <span class="material-symbols-outlined text-[18px]">schedule</span>
          <span>{{ prepTime }} min</span>
        </div>
      </div>

      <!-- Action Button Pinned to Bottom -->
      <div class="mt-auto pt-2">
        <button
          :class="[
            'font-label-md flex w-full items-center justify-center gap-2 rounded-xl py-3 text-base font-semibold transition-all duration-200 active:scale-[0.98]',
            buttonClass,
          ]"
          @click="emit('toggle-cooked')"
        >
          <span v-if="isPrepared" class="material-symbols-outlined text-[20px]">
            check_circle
          </span>
          {{ btnText }}
        </button>
      </div>
    </div>
  </div>
</template>
