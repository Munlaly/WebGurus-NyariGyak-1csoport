<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import type { PageProps } from '@inertiajs/core';
import {
  stepGoalSchema,
  stepDietSchema,
  stepDislikedIngredientsSchema,
  stepMetabolismSchema,
  stepHouseholdSchema,
  stepPrepTimeSchema,
  stepExerciseSchema,
  type QuizFormData,
  quizFormSchema,
} from '../Schemas/quizSchema';

import StepIntro from '../Components/QuizSteps/StepIntro.vue';
import StepGoal from '../Components/QuizSteps/StepGoal.vue';
import StepMetabolism from '../Components/QuizSteps/StepMetabolism.vue';
import StepDiet from '../Components/QuizSteps/StepDiet.vue';
import StepPrepTime from '../Components/QuizSteps/StepPrepTime.vue';
import StepHousehold from '../Components/QuizSteps/StepHousehold.vue';
import StepDislikedIngredients from '../Components/QuizSteps/StepDislikedIngredients.vue';
import StepExercise from '../Components/QuizSteps/StepExercise.vue';
import StepSummary from '../Components/QuizSteps/StepSummary.vue';

interface QuizPageProps extends PageProps {
  auth: {
    user: {
      username: string;
    } | null;
  };
  dietaryOptions: { id: number; name: string; description: string | null }[];
}

const dislikedIngredientsObjects = ref<{ id: number; label: string }[]>([]);

watch(
  dislikedIngredientsObjects,
  (newSelection) => {
    form.disliked_ingredients = newSelection.map((item) => item.id);
  },
  { deep: true },
);

// Grab data from shared props
const page = usePage<QuizPageProps>();
const username = computed(() => page.props.auth?.user?.username || 'Guest');
const dietaryOptions = computed(() => page.props.dietaryOptions || []);

const stepConfig = [
  { type: 'intro', schema: null },
  { type: 'question', schema: stepGoalSchema },
  { type: 'question', schema: stepMetabolismSchema },
  { type: 'question', schema: stepExerciseSchema },
  { type: 'question', schema: stepDietSchema },
  { type: 'question', schema: stepDislikedIngredientsSchema },
  { type: 'question', schema: stepPrepTimeSchema },
  { type: 'question', schema: stepHouseholdSchema },
  { type: 'summary', schema: quizFormSchema },
];

const currentStep = ref(0);

const totalQuestions = computed(
  () => stepConfig.filter((s) => s.type === 'question').length,
);

const form = useForm({
  fitness_goal: '' as QuizFormData['fitness_goal'],
  meal_plan_preferences: [] as number[],
  disliked_ingredients: [] as number[],
  sex: '' as QuizFormData['sex'],
  birthdate: '',
  height_cm: '' as unknown as number,
  weight_kg: '' as unknown as number,
  baseline_activity: '' as QuizFormData['baseline_activity'],
  household_size: '' as unknown as number,
  prep_time_preference: '' as unknown as number,
  exercise_schedule: {
    monday: 'rest',
    tuesday: 'rest',
    wednesday: 'rest',
    thursday: 'rest',
    friday: 'rest',
    saturday: 'rest',
    sunday: 'rest',
  } as QuizFormData['exercise_schedule'],
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

const nextButtonLabel = computed(() => {
  if (stepConfig[currentStep.value].type === 'summary') return 'Submit';
  if (stepConfig[currentStep.value + 1]?.type === 'summary') return 'View Plan';
  return 'Next';
});

const nextButtonIcon = computed(() => {
  return stepConfig[currentStep.value].type === 'summary'
    ? 'i-heroicons-check'
    : 'i-heroicons-arrow-right';
});

const isSubmitDisabled = computed(() => {
  if (stepConfig[currentStep.value].type === 'summary') {
    return !quizFormSchema.safeParse(form).success;
  }
  return false;
});

function prevStep() {
  if (currentStep.value > 0) {
    currentStep.value--;
  }
}

function handleNext() {
  if (stepConfig[currentStep.value]?.type === 'summary') {
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

      <StepExercise
        v-else-if="currentStep === 3"
        v-model="form.exercise_schedule"
      />

      <StepDiet
        v-else-if="currentStep === 4"
        v-model="form.meal_plan_preferences"
        :options="dietaryOptions"
      />

      <StepDislikedIngredients
        v-else-if="currentStep === 5"
        v-model="dislikedIngredientsObjects"
      />

      <StepPrepTime
        v-else-if="currentStep === 6"
        v-model="form.prep_time_preference"
      />

      <StepHousehold
        v-else-if="currentStep === 7"
        v-model="form.household_size"
      />

      <StepSummary
        v-else-if="currentStep === 8"
        :form="form"
        :dietary-options="dietaryOptions"
        :disliked-ingredients="dislikedIngredientsObjects"
      />
    </main>

    <!-- BOTTOM NAVIGATION -->
    <div
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

        <UButton
          type="submit"
          color="primary"
          :trailing-icon="nextButtonIcon"
          size="lg"
          :disabled="isSubmitDisabled"
        >
          {{ nextButtonLabel }}
        </UButton>
      </nav>
    </div>
  </UForm>
</template>
