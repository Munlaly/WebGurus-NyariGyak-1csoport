<script setup lang="ts">
import { computed } from 'vue';
import type { QuizFormData } from '../../Schemas/quizSchema';

const props = defineProps<{
  form: QuizFormData;
  dietaryOptions: { id: number; name: string; description: string | null }[];
  dislikedIngredients: { id: number; label: string }[];
}>();

// Data Mapping Helpers
const formattedGoal = computed(() => {
  const map: Record<string, string> = {
    lose_weight: 'Lose Weight',
    maintain: 'Maintain Form',
    gain_muscle: 'Gain Muscle',
  };
  return map[props.form.fitness_goal] || props.form.fitness_goal;
});

const selectedDiets = computed(() => {
  if (props.form.meal_plan_preferences.length === 0) return ['None'];
  return props.form.meal_plan_preferences.map((id) => {
    return (
      props.dietaryOptions.find((d) => d.id === id)?.name || `Unknown (${id})`
    );
  });
});

function formatActivity(activity: string) {
  return activity
    .split('_')
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ');
}
</script>

<template>
  <div class="flex w-full flex-col items-center space-y-10">
    <div class="max-w-2xl space-y-4 text-center">
      <h2 class="font-display text-4xl font-bold tracking-tight text-slate-900">
        Review Your Profile
      </h2>
      <p class="text-lg leading-relaxed text-slate-700">
        Double-check your metrics before we generate your customized meal plan.
        You can go back to adjust anything that looks off.
      </p>
    </div>

    <div
      class="grid w-full max-w-5xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
    >
      <!-- Core Objective -->
      <UCard class="flex h-full flex-col">
        <template #header>
          <div
            class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"
          >
            <UIcon name="i-heroicons-flag" class="text-primary h-5 w-5" />
            Core Objective
          </div>
        </template>
        <div class="space-y-4 text-sm">
          <div
            class="flex justify-between border-b border-slate-200 pb-2 dark:border-gray-700"
          >
            <span class="text-slate-600">Fitness Goal</span>
            <span class="text-primary font-semibold">{{ formattedGoal }}</span>
          </div>
          <div
            class="flex justify-between border-b border-slate-200 pb-2 dark:border-gray-700"
          >
            <span class="text-slate-600">Max Prep Time</span>
            <span class="font-semibold text-slate-900"
              >{{ form.prep_time_preference }} mins</span
            >
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Household Size</span>
            <span class="font-semibold text-slate-900"
              >{{ form.household_size }} person(s)</span
            >
          </div>
        </div>
      </UCard>

      <!-- Card 2: Biometrics -->
      <UCard class="flex h-full flex-col">
        <template #header>
          <div class="flex items-center gap-2 text-lg font-bold text-slate-900">
            <UIcon name="i-heroicons-user" class="text-primary h-5 w-5" />
            Biometrics
          </div>
        </template>
        <div class="space-y-4 text-sm">
          <div class="flex justify-between border-b border-slate-200 pb-2">
            <span class="text-slate-600">Sex</span>
            <span class="font-semibold text-slate-900 capitalize">{{
              form.sex
            }}</span>
          </div>
          <div
            class="flex justify-between border-b border-slate-200 pb-2 dark:border-gray-700"
          >
            <span class="text-slate-600">Height / Weight</span>
            <span class="font-semibold text-slate-900"
              >{{ form.height_cm }} cm / {{ form.weight_kg }} kg</span
            >
          </div>
          <div
            class="flex justify-between border-b border-slate-200 pb-2 dark:border-gray-700"
          >
            <span class="text-slate-600">Birthdate</span>
            <span class="font-semibold text-slate-900">{{
              form.birthdate
            }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-600">Baseline Activity</span>
            <span class="font-semibold text-slate-900">{{
              formatActivity(form.baseline_activity)
            }}</span>
          </div>
        </div>
      </UCard>

      <!-- Card 3: Nutrition Constraints -->
      <UCard class="flex h-full flex-col">
        <template #header>
          <div class="flex items-center gap-2 text-lg font-bold text-slate-900">
            <UIcon name="i-heroicons-no-symbol" class="text-primary h-5 w-5" />
            Nutrition Constraints
          </div>
        </template>
        <div class="space-y-4 text-sm">
          <div>
            <span class="mb-2 block text-slate-600">Dietary Preferences</span>
            <div class="flex flex-wrap gap-2">
              <UBadge
                v-for="diet in selectedDiets"
                :key="diet"
                color="neutral"
                variant="subtle"
                size="md"
              >
                {{ diet }}
              </UBadge>
            </div>
          </div>
          <div class="pt-2">
            <span class="mb-2 block text-slate-600">Excluded Ingredients</span>
            <div
              v-if="dislikedIngredients.length > 0"
              class="flex flex-wrap gap-2"
            >
              <UBadge
                v-for="item in dislikedIngredients"
                :key="item.id"
                color="error"
                variant="subtle"
                size="md"
              >
                {{ item.label }}
              </UBadge>
            </div>
            <span v-else class="font-semibold text-slate-900">None</span>
          </div>
        </div>
      </UCard>
    </div>
  </div>
</template>
