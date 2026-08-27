<script setup lang="ts">
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

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement | null;

  if (target) {
    target.src = 'https://placehold.co/600x400?text=No+Image';
  }
};

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
        class="bg-surface-container-highest text-on-surface flex items-center gap-2 rounded-lg px-4 py-2 font-semibold shadow-lg"
      >
        <span class="material-symbols-outlined animate-spin">sync</span>
        Queued for Reroll
      </div>
    </div>

    <!-- Image Area -->
    <div
      class="bg-surface-container relative aspect-video w-full overflow-hidden"
    >
      <img
        :src="imageUrl || 'https://placehold.co/600x400?text=No+Image'"
        :alt="imageAlt"
        class="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
        @error="handleImageError"
      />
      <!-- Pinned Badge -->
      <div
        v-if="isPinned"
        class="bg-primary text-on-primary absolute top-2 right-2 flex items-center gap-1 rounded-md px-2 py-1 text-xs font-bold shadow-sm"
      >
        <span class="material-symbols-outlined text-[14px]">push_pin</span>
        Pinned
      </div>
    </div>

    <!-- Content Area -->
    <div class="flex flex-1 flex-col p-4">
      <!-- Meal Type -->
      <span
        class="text-primary mb-1 text-xs font-bold tracking-wider uppercase"
      >
        {{ mealType }}
      </span>

      <!-- Title -->
      <h4
        class="text-on-surface mb-3 line-clamp-2 min-h-12 text-lg leading-tight font-bold"
      >
        {{ title }}
      </h4>

      <!-- Meta Tags (Calories & Extras) -->
      <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
        <div
          class="bg-surface-container-low text-on-surface-variant rounded-md px-2 py-1 font-medium"
        >
          {{ calories }} kcal
        </div>
        <div
          v-for="tag in tags"
          :key="tag"
          class="text-on-surface-variant flex items-center gap-1 rounded-md px-2 py-1 font-medium"
        >
          {{ tag }}
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-auto grid grid-cols-2 gap-2 pt-2">
        <button
          :class="[
            'flex items-center justify-center gap-1.5 rounded-lg border py-2 text-sm font-semibold transition-colors',
            pinButtonClass,
          ]"
          @click="emit('toggle-pin', id)"
        >
          <span
            class="material-symbols-outlined text-[18px]"
            :class="{ 'fill-current': isPinned }"
          >
            push_pin
          </span>
          Pin
        </button>

        <button
          :disabled="isRolling || isPinned"
          class="bg-error-50 text-error-700 hover:bg-error-100 dark:bg-error-900/20 dark:text-error-400 flex items-center justify-center gap-1.5 rounded-lg py-2 text-sm font-semibold transition-colors disabled:cursor-not-allowed disabled:opacity-50"
          @click="emit('reroll', id, mealType)"
        >
          <span class="material-symbols-outlined text-[18px]">sync</span>
          Reroll
        </button>
      </div>
    </div>
  </div>
</template>
