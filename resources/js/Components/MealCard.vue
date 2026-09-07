<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    id: number;
    title: string;
    calories: number;
    prepTime: number;
    imageUrl: string;
    imageAlt: string;
    isPrepared: boolean;
    isFavorite?: boolean;
  }>(),
  {
    isFavorite: false,
  },
);

const emit = defineEmits<{
  (e: 'toggle-cooked'): void;
  (e: 'toggle-favorite'): void;
}>();

const btnText = computed(() =>
  props.isPrepared ? 'Cooked (Click to undo)' : 'Mark as Cooked',
);

const buttonClass = computed(() =>
  props.isPrepared
    ? 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high border border-outline-variant'
    : 'bg-primary text-on-primary hover:bg-primary/90 dark:hover:bg-[#b080ea] shadow-sm',
);

const imageStateClass = computed(() =>
  props.isPrepared ? 'opacity-50 grayscale-[40%]' : '',
);

const contentStateClass = computed(() =>
  props.isPrepared ? 'opacity-75' : '',
);

// Favorite Button Computed Properties
const favoriteButtonClass = computed(() =>
  props.isFavorite
    ? 'bg-red-500 border-red-500 text-white shadow-sm hover:bg-red-600 hover:border-red-600'
    : 'bg-surface-container-low border-outline-variant/40 text-on-surface-variant hover:border-red-300 hover:bg-red-50/50 dark:hover:bg-red-950/20 hover:text-red-500',
);

const favoriteTooltipText = computed(() =>
  props.isFavorite ? 'Remove like' : 'Like this meal',
);
</script>

<template>
  <div
    class="bg-surface-container-lowest group relative flex cursor-pointer flex-col overflow-hidden rounded-2xl shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-300 hover:scale-[1.01] hover:shadow-[0px_12px_32px_rgba(0,0,0,0.08)]"
  >
    <!-- Image Header with 16:9 Aspect Ratio -->
    <Link
      :href="route('recipe.show', id)"
      class="bg-surface-container relative aspect-video w-full overflow-hidden"
    >
      <!-- Floating Cooked Status Badge (Top Right) -->
      <div
        v-if="isPrepared"
        class="bg-surface-container-lowest/90 text-primary font-label-md text-label-md absolute top-3 right-3 z-10 flex items-center gap-1 rounded-full px-3 py-1 shadow-md backdrop-blur-sm"
      >
        <span class="material-symbols-outlined text-[16px]">check_circle</span>
        Cooked
      </div>

      <!-- Hero Image -->
      <div
        :class="[
          'bg-surface-container-low relative aspect-video w-full overflow-hidden transition-all duration-500',
          imageStateClass,
        ]"
      >
        <!-- 1. Ambient Blur Background: Fills the empty space using a heavy blur so pixelation disappears -->
        <img
          class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-50 blur-xl"
          :src="imageUrl"
          alt=""
        />

        <!-- 2. Crisp Foreground Image: Stays uncropped (object-contain) with your hover effects -->
        <img
          class="relative h-full w-full object-contain drop-shadow-md transition-all duration-500 group-hover:scale-105"
          :alt="imageAlt"
          :src="imageUrl"
        />
      </div>
    </Link>

    <!-- Content Body -->
    <div :class="['flex flex-1 flex-col p-6', contentStateClass]">
      <h3
        class="text-on-surface mb-4 line-clamp-2 min-h-14 text-xl leading-snug font-bold tracking-tight md:text-2xl"
      >
        {{ title }}
      </h3>

      <!-- Structured Macro / Meta Pills + Favorite Button Row -->
      <div class="mb-4 flex items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
          <div
            class="bg-surface-container text-on-surface flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-semibold"
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

        <!-- Like / Favorite Action with Hover Tooltip -->
        <div class="relative flex items-center">
          <button
            type="button"
            class="group/like relative flex h-9 w-9 items-center justify-center rounded-lg border transition-all duration-200 hover:scale-105 active:scale-95"
            :class="favoriteButtonClass"
            :aria-label="favoriteTooltipText"
            @click.stop="emit('toggle-favorite')"
          >
            <span
              v-if="isFavorite"
              class="material-symbols-outlined text-[20px] transition-transform duration-200 [font-variation-settings:'FILL'_1] group-hover/like:scale-110"
            >
              favorite
            </span>
            <span
              v-else
              class="material-symbols-outlined text-[20px] transition-transform duration-200 group-hover/like:scale-110"
            >
              favorite
            </span>

            <!-- Tooltip Text on Hover -->
            <span
              class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 rounded-md bg-gray-900 px-2 py-0.5 text-xs whitespace-nowrap text-white opacity-0 shadow-sm transition-opacity duration-150 group-hover/like:opacity-100 dark:bg-gray-700"
            >
              {{ favoriteTooltipText }}
            </span>
          </button>
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
