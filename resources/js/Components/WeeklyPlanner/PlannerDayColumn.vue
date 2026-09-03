<script setup lang="ts">
import PlannerMealCard from './PlannerMealCard.vue';
import type { PlannerMeal } from '../../Types/plannerInterfaces.js';

defineProps<{
  dayName: string;
  totalCalories: number;
  perfectMatch?: boolean;
  meals: PlannerMeal[];
}>();

const emit = defineEmits<{
  (e: 'toggle-pin', dayName: string, mealId: number): void;
  (e: 'reroll', dayName: string, mealId: number, mealType: string): void;
}>();

function formatTags(meal: PlannerMeal) {
  const tags: string[] = [];
  if (meal.prep_time_minutes) {
    tags.push(`${meal.prep_time_minutes}m`);
  }
  if (meal.diets && Array.isArray(meal.diets)) {
    if (meal.diets.length > 0) tags.push(meal.diets[0]);
  }
  return tags;
}
</script>

<template>
  <div class="flex w-full min-w-0 flex-col">
    <!-- Day Header -->
    <div
      class="bg-background sticky top-0 z-30 mb-6 flex items-end justify-between border-b pt-2 pb-4 md:static md:bg-transparent md:pt-0"
    >
      <h2 class="text-on-surface text-2xl font-bold tracking-tight">
        {{ dayName }}
      </h2>

      <div class="flex flex-col items-end">
        <span class="text-on-surface-variant text-sm font-semibold">
          {{ totalCalories }} kcal
        </span>
        <span
          v-if="!perfectMatch"
          class="text-error text-[10px] font-bold tracking-wider uppercase"
          title="We had to stretch your calorie window to find meals."
        >
          Calorie Mismatch
        </span>
      </div>
    </div>

    <!-- Meals Grid (Transforms to Grid on Desktop) -->
    <div
      class="grid min-w-0 grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
    >
      <PlannerMealCard
        v-for="meal in meals"
        :id="meal.id"
        :key="meal.id + '-' + meal.meal_type"
        :meal-type="meal.meal_type"
        :title="meal.name"
        :calories="meal.calories"
        :image-url="meal.image"
        :tags="formatTags(meal)"
        :is-pinned="meal.isPinned"
        :is-rolling="meal.isRolling"
        @toggle-pin="emit('toggle-pin', dayName, meal.id)"
        @reroll="(id, type) => emit('reroll', dayName, id, type)"
      />
    </div>
  </div>
</template>
