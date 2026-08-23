<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import type { PageProps } from '@inertiajs/core';
import {
  stepGoalSchema,
  stepDietSchema,
  stepDislikedIngredientsSchema,
  stepMetabolismSchema,
  stepHouseholdSchema,
  stepPrepTimeSchema,
  type QuizFormData,
} from '../Schemas/quizSchema';

import StepIntro from '../Components/QuizsSteps/StepIntro.vue';
import StepGoal from '../Components/QuizsSteps/StepGoal.vue';
import StepMetabolism from '../Components/QuizsSteps/StepMetabolism.vue';
import StepDiet from '../Components/QuizsSteps/StepDiet.vue';
import StepPrepTime from '../Components/QuizsSteps/StepPrepTime.vue';
import StepHousehold from '../Components/QuizsSteps/StepHousehold.vue';

interface QuizPageProps extends PageProps {
  auth: {
    user: {
      username: string;
    } | null;
  };
  dietaryOptions: { id: number; name: string; description: string | null }[];
}

// Grab data from shared props
const page = usePage<QuizPageProps>();
const username = computed(() => page.props.auth?.user?.username || 'Guest');
const dietaryOptions = computed(() => page.props.dietaryOptions || []);

const stepConfig = [
  { type: 'intro', schema: null },
  { type: 'question', schema: stepGoalSchema },
  { type: 'question', schema: stepMetabolismSchema },
  { type: 'question', schema: stepDietSchema },
  { type: 'question', schema: stepDislikedIngredientsSchema },
  { type: 'question', schema: stepPrepTimeSchema },
  { type: 'question', schema: stepHouseholdSchema },
  { type: 'summary', schema: null },
];

const currentStep = ref(0);

const totalQuestions = computed(
  () => stepConfig.filter((s) => s.type === 'question').length,
);

const form = useForm({
  fitness_goal: '' as QuizFormData['fitness_goal'],
  meal_plan_preferences: [],
  disliked_ingredients: [],
  sex: '' as QuizFormData['sex'],
  birthdate: '',
  height_cm: '' as unknown as number,
  weight_kg: '' as unknown as number,
  baseline_activity: '' as QuizFormData['baseline_activity'],
  household_size: '' as unknown as number,
  prep_time_preference: '' as unknown as number,
});

const progressPercentage = computed(() => {
  const step = stepConfig[currentStep.value];

  if (step.type === 'intro') return 0;
  if (step.type === 'summary') return 100;

  const completedQuestions = stepConfig
    .slice(0, currentStep.value + 1)
    .filter((s) => s.type === 'question').length;

  return Math.round((completedQuestions / totalQuestions.value) * 100);
});

function prevStep() {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
}

function handleNext() {
  if (stepConfig[currentStep.value]?.type === 'summery') {
    form.post(route('quiz.store'));
  } else if (currentStep.value < stepConfig.length - 1) {
    currentStep.value++;
  }
}
</script>

<template>
  <UForm
    :state="form"
    :schema="stepConfig[currentStep].schema || undefined"
    class="bg-background text-on-background flex min-h-screen flex-col antialiased"
    @submit="handleNext"
  >
    <!-- HEADER & PROGRESS BAR -->
    <header
      v-if="stepConfig[currentStep].type === 'question'"
      class="px-gutter max-w-container-max relative z-10 mx-auto mt-4 flex w-full shrink-0 flex-col items-center pt-8 md:mt-8"
    >
      <div class="w-full max-w-3xl">
        <div class="mb-2 flex items-center justify-between">
          <span
            class="font-label-md text-label-md text-on-surface-variant tracking-wider uppercase"
          >
            Step {{ currentStep }} of {{ totalQuestions }}
          </span>
          <span class="font-label-md text-label-md text-primary font-bold">
            {{ progressPercentage }}%
          </span>
        </div>

        <UProgress v-model="progressPercentage" :max="100" />
      </div>
    </header>

    <!-- MAIN CONTENT AREA -->
    <main
      class="relative mx-auto mb-20 flex w-full max-w-7xl flex-1 flex-col items-center justify-center px-4 py-12"
    >
      <StepIntro v-if="currentStep === 0" :username="username" />

      <StepGoal v-else-if="currentStep === 1" v-model="form.fitness_goal" />

      <StepMetabolism
        v-else-if="currentStep === 2"
        v-model:sex="form.sex"
        v-model:birthdate="form.birthdate"
        v-model:height="form.height_cm"
        v-model:weight="form.weight_kg"
        v-model:activity="form.baseline_activity"
      />

      <StepDiet
        v-else-if="currentStep === 3"
        v-model="form.meal_plan_preferences"
        :options="dietaryOptions"
      />

      <StepPrepTime
        v-else-if="currentStep === 5"
        v-model="form.prep_time_preference"
      />

      <StepHousehold
        v-else-if="currentStep === 6"
        v-model="form.household_size"
      />
    </main>

    <!-- BOTTOM NAVIGATION -->
    <div
      v-if="stepConfig[currentStep].type !== 'summary'"
      class="bg-surface border-surface-container-highest fixed bottom-0 left-0 z-50 w-full shrink-0 border-t shadow-sm"
    >
      <nav
        class="px-gutter max-w-container-max mx-auto flex h-20 items-center justify-between py-4"
      >
        <UButton
          v-if="currentStep > 0"
          variant="ghost"
          color="gray"
          icon="i-heroicons-arrow-left"
          size="lg"
          @click="prevStep"
        >
          Back
        </UButton>
        <div v-else></div>
        <!-- Spacer -->

        <UButton
          type="submit"
          color="primary"
          trailing-icon="i-heroicons-arrow-right"
          size="lg"
        >
          {{
            stepConfig[currentStep + 1]?.type === 'summary'
              ? 'View Plan'
              : 'Next'
          }}
        </UButton>
      </nav>
    </div>
  </UForm>
</template>
