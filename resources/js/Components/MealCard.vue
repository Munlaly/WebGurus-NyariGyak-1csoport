<script setup lang="ts">
import { computed } from 'vue';
const props = defineProps<{
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
    ? 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high border border-outline-variant '
    : 'bg-primary-container text-on-primary hover:bg-primary-fixed',
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
    class="bg-surface-container-lowest group flex cursor-pointer flex-col overflow-hidden rounded-xl shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-300 hover:scale-[1.02] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]"
  >
    <!-- Image Header -->
    <div class="bg-surface-container h-40 w-full overflow-hidden">
      <!-- Floating Status Badge -->
      <div
        v-if="isPrepared"
        class="bg-surface-container-lowest text-primary font-label-md text-label-md absolute top-3 right-3 z-10 flex items-center gap-1 rounded-full px-3 py-1 shadow-sm"
      >
        <span class="material-symbols-outlined text-[14px]">check_circle</span>
        Cooked
      </div>

      <!-- Image -->
      <img
        :class="[
          'h-full w-full object-cover transition-all duration-500 group-hover:scale-105',
          imageStateClass,
        ]"
        :alt="imageAlt"
        :src="imageUrl"
      />
    </div>

    <!-- Content Body -->
    <div :class="['flex flex-1 flex-col p-6', contentStateClass]">
      <h3 class="font-headline-md text-headline-md text-on-surface mb-2">
        {{ title }}
      </h3>

      <!-- Meta Stats -->
      <div
        class="text-on-surface-variant font-body-sm text-body-sm mb-6 flex items-center gap-4"
      >
        <span class="flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]"
            >local_fire_department</span
          >
          {{ calories }} kcal
        </span>
        <span class="flex items-center gap-1">
          <span class="material-symbols-outlined text-[16px]">schedule</span>
          {{ prepTime }} min
        </span>
      </div>

      <!-- Action Button -->
      <div class="mt-auto">
        <button
          :class="[
            'font-label-md text-label-md flex w-full items-center justify-center gap-2 rounded-lg py-2.5 transition-colors',
            buttonClass,
          ]"
          @click="emit('toggle-cooked')"
        >
          <span v-if="isPrepared" class="material-symbols-outlined text-[18px]"
            >check_circle
          </span>
          {{ btnText }}
        </button>
      </div>
    </div>
  </div>
</template>
