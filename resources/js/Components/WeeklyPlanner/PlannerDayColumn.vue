<script setup lang="ts">
import PlannerMealCard from './PlannerMealCard.vue';

export interface PlannerMeal {
  id: number;
  meal_type: string;
  name: string;
  calories: number;
  image?: string;
  prep_time_minutes?: number;
  diets?: string[];
  isPinned?: boolean;
  isRolling?: boolean;
}

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

const formatTags = (meal: PlannerMeal) => {
  const tags: string[] = [];
  if (meal.prep_time_minutes) {
    tags.push(`${meal.prep_time_minutes}m`);
  }
  if (meal.diets && Array.isArray(meal.diets)) {
    if (meal.diets.length > 0) tags.push(meal.diets[0]);
  }
  return tags;
};
</script>

<template>
  <div class="flex w-full shrink-0 flex-col md:w-[320px]">
    <!-- Day Header -->
    <div
      class="bg-background sticky top-0 z-30 mb-4 flex items-end justify-between border-b pt-2 pb-2 md:static md:bg-transparent md:pt-0"
    >
      <h2 class="text-on-surface text-2xl font-bold tracking-tight">
        {{ dayName }}
      </h2>

      <div class="flex flex-col items-end">
        <span class="text-on-surface-variant text-sm font-semibold">
          {{ totalCalories }} kcal
        </span>
        <!-- Warning indicator if algorithm struggled to hit target window -->
        <span
          v-if="!perfectMatch"
          class="text-error text-[10px] font-bold tracking-wider uppercase"
          title="We had to stretch your calorie window to find meals."
        >
          Calorie Mismatch
        </span>
      </div>
    </div>

    <!-- Meals Stack -->
    <div class="flex flex-col gap-4">
      <PlannerMealCard
        v-for="meal in meals"
        :id="meal.id"
        :key="meal.id"
        :meal-type="meal.meal_type"
        :title="meal.name"
        :calories="meal.calories"
        :image-url="meal.image || '/images/meal-placeholder.jpg'"
        :tags="formatTags(meal)"
        :is-pinned="meal.isPinned"
        :is-rolling="meal.isRolling"
        @toggle-pin="emit('toggle-pin', dayName, meal.id)"
        @reroll="(id, type) => emit('reroll', dayName, id, type)"
      />
    </div>
  </div>
</template>
