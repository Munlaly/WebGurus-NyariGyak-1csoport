<!-- PlannerMealCard.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    id: number;
    mealType: string;
    title: string;
    calories: number;
    imageUrl?: string;
    imageAlt?: string;
    tags?: string[];
    isPinned?: boolean;
    isRolling?: boolean;
  }>(),
  {
    imageUrl: 'https://placehold.co/600x400?text=No+Image',
    imageAlt: 'Meal image',
    tags: () => [],
    isPinned: false,
    isRolling: false,
  },
);

const emit = defineEmits<{
  (e: 'toggle-pin', id: number): void;
  (e: 'reroll', id: number, type: string): void;
}>();

const pinButtonClass = computed(() =>
  props.isPinned
    ? 'bg-primary-50 text-primary-700 border-primary-200 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 dark:border-primary-800'
    : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high border-transparent',
);

const cardStateClass = computed(() =>
  props.isPinned
    ? 'border-primary shadow-[0px_0px_0px_1px_rgba(var(--color-primary),1)]'
    : 'border-outline-variant/50 hover:shadow-md',
);

function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement | null;

  if (target) {
    target.src = 'https://placehold.co/600x400?text=No+Image';
  }
}
</script>

<template>
  <div
    :class="[
      'bg-surface-container-lowest relative flex flex-col overflow-hidden rounded-2xl border transition-all duration-300',
      cardStateClass,
    ]"
  >
    <!-- Reroll Loading Overlay -->
    <div
      v-if="isRolling"
      class="bg-background/60 absolute inset-0 z-20 flex items-center justify-center backdrop-blur-[2px]"
    >
      <div
        class="bg-surface-container-highest text-on-surface flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold shadow-lg sm:px-4 sm:py-2 sm:text-base"
      >
        <span
          class="material-symbols-outlined animate-spin text-[18px] sm:text-[24px]"
          >sync</span
        >
        Queued
      </div>
    </div>

    <!-- Image Area -->
    <div
      class="bg-surface-container relative aspect-video w-full overflow-hidden"
    >
      <Link :href="route('recipe.show', props.id)">
        <img
          :src="imageUrl || 'https://placehold.co/600x400?text=No+Image'"
          :alt="imageAlt"
          class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
          @error="handleImageError"
        />
      </Link>
      <!-- Pinned Badge -->
      <div
        v-if="isPinned"
        class="bg-primary text-on-primary absolute top-2 right-2 flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[10px] font-bold shadow-sm sm:px-2 sm:py-1 sm:text-xs"
      >
        <span class="material-symbols-outlined text-[12px] sm:text-[14px]"
          >push_pin</span
        >
        Pinned
      </div>
    </div>

    <!-- Content Area: Reduced padding on mobile (p-3) -->
    <div class="flex flex-1 flex-col p-3 sm:p-4">
      <!-- Meal Type -->
      <span
        class="text-primary mb-1 text-[10px] font-bold tracking-wider uppercase sm:text-xs"
      >
        {{ mealType }}
      </span>

      <!-- Title: Smaller font on mobile -->
      <h4
        class="text-on-surface mb-2 line-clamp-2 min-h-10 text-base leading-tight font-bold sm:mb-3 sm:min-h-12 sm:text-lg"
      >
        {{ title }}
      </h4>

      <!-- Meta Tags (Calories & Extras) -->
      <div
        class="mb-3 flex flex-wrap items-center gap-1.5 text-xs sm:mb-4 sm:gap-2 sm:text-sm"
      >
        <div
          class="bg-surface-container-low text-on-surface-variant rounded-md px-1.5 py-0.5 font-medium sm:px-2 sm:py-1"
        >
          {{ calories }} kcal
        </div>
        <div
          v-for="tag in tags"
          :key="tag"
          class="text-on-surface-variant flex items-center gap-1 rounded-md px-1.5 py-0.5 font-medium sm:px-2 sm:py-1"
        >
          {{ tag }}
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-auto grid grid-cols-2 gap-2 pt-2">
        <button
          :class="[
            'flex items-center justify-center gap-1 rounded-lg border py-1.5 text-xs font-semibold transition-colors sm:gap-1.5 sm:py-2 sm:text-sm',
            pinButtonClass,
          ]"
          @click="emit('toggle-pin', id)"
        >
          <span
            class="material-symbols-outlined text-[16px] sm:text-[18px]"
            :class="{ 'fill-current': isPinned }"
          >
            push_pin
          </span>
          Pin
        </button>

        <button
          :disabled="isRolling || isPinned"
          class="bg-error-50 text-error-700 hover:bg-error-100 dark:bg-error-900/20 dark:text-error-400 flex items-center justify-center gap-1 rounded-lg py-1.5 text-xs font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-50 sm:gap-1.5 sm:py-2 sm:text-sm"
          @click="emit('reroll', id, mealType)"
        >
          <span class="material-symbols-outlined text-[16px] sm:text-[18px]"
            >sync</span
          >
          Reroll
        </button>
      </div>
    </div>
  </div>
</template>
