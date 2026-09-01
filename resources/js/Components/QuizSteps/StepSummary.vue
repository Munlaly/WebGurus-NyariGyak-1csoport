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

function formatActivity (activity: string){
  return activity
    .split('_')
    .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
    .join(' ');
};
</script>

<template>
  <div class="flex w-full flex-col items-center space-y-10">
    <div class="max-w-2xl space-y-4 text-center">
      <h2
        class="font-display text-on-surface text-4xl font-bold tracking-tight"
      >
        Review Your Profile
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        Double-check your metrics before we generate your customized meal plan.
        You can go back to adjust anything that looks off.
      </p>
    </div>

    <div
      class="grid w-full max-w-5xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
    >
      <!-- Card 1: Core Objective -->
      <UCard class="flex h-full flex-col">
        <template #header>
          <div
            class="text-on-surface flex items-center gap-2 text-lg font-bold"
          >
            <UIcon name="i-heroicons-flag" class="text-primary h-5 w-5" />
            Core Objective
          </div>
        </template>
        <div class="space-y-4 text-sm">
          <div class="flex justify-between border-b pb-2">
            <span class="text-on-surface-variant">Fitness Goal</span>
            <span class="text-primary font-semibold">{{ formattedGoal }}</span>
          </div>
          <div class="flex justify-between border-b pb-2">
            <span class="text-on-surface-variant">Max Prep Time</span>
            <span class="font-semibold"
              >{{ form.prep_time_preference }} mins</span
            >
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Household Size</span>
            <span class="font-semibold"
              >{{ form.household_size }} person(s)</span
            >
          </div>
        </div>
      </UCard>

      <!-- Card 2: Biometrics -->
      <UCard class="flex h-full flex-col">
        <template #header>
          <div
            class="text-on-surface flex items-center gap-2 text-lg font-bold"
          >
            <UIcon name="i-heroicons-user" class="text-primary h-5 w-5" />
            Biometrics
          </div>
        </template>
        <div class="space-y-4 text-sm">
          <div class="flex justify-between border-b pb-2">
            <span class="text-on-surface-variant">Sex</span>
            <span class="font-semibold capitalize">{{ form.sex }}</span>
          </div>
          <div class="flex justify-between border-b pb-2">
            <span class="text-on-surface-variant">Height / Weight</span>
            <span class="font-semibold"
              >{{ form.height_cm }} cm / {{ form.weight_kg }} kg</span
            >
          </div>
          <div class="flex justify-between border-b pb-2">
            <span class="text-on-surface-variant">Birthdate</span>
            <span class="font-semibold">{{ form.birthdate }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Baseline Activity</span>
            <span class="font-semibold">{{
              formatActivity(form.baseline_activity)
            }}</span>
          </div>
        </div>
      </UCard>

      <!-- Card 3: Nutrition Constraints -->
      <UCard class="flex h-full flex-col">
        <template #header>
          <div
            class="text-on-surface flex items-center gap-2 text-lg font-bold"
          >
            <UIcon name="i-heroicons-no-symbol" class="text-primary h-5 w-5" />
            Nutrition Constraints
          </div>
        </template>
        <div class="space-y-4 text-sm">
          <div>
            <span class="text-on-surface-variant mb-2 block"
              >Dietary Preferences</span
            >
            <div class="flex flex-wrap gap-1">
              <UBadge
                v-for="diet in selectedDiets"
                :key="diet"
                color="gray"
                variant="subtle"
              >
                {{ diet }}
              </UBadge>
            </div>
          </div>
          <div class="pt-2">
            <span class="text-on-surface-variant mb-2 block"
              >Excluded Ingredients</span
            >
            <div
              v-if="dislikedIngredients.length > 0"
              class="flex flex-wrap gap-1"
            >
              <UBadge
                v-for="item in dislikedIngredients"
                :key="item.id"
                color="red"
                variant="subtle"
              >
                {{ item.label }}
              </UBadge>
            </div>
            <span v-else class="text-on-surface font-semibold">None</span>
          </div>
        </div>
      </UCard>
    </div>
  </div>
</template>
