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

import vegetables_bg from '../../images/verdant_bg.jpg';

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
    class="flex min-h-screen flex-col antialiased"
    @submit="handleNext"
  >
    <!-- 1. Background fixed to viewport to prevent scroll cutoff -->
    <div class="fixed inset-0 z-0 overflow-hidden bg-gray-900">
      <img
        :src="vegetables_bg"
        alt="Fresh healthy food"
        class="h-full w-full object-cover opacity-90"
      />
      <div class="absolute inset-0 bg-black/10"></div>
    </div>

    <main
      class="relative z-10 flex flex-1 items-start justify-center px-4 pt-8 pb-24 lg:pt-16 lg:pb-28"
    >
      <div
        class="my-auto w-full max-w-4xl rounded-3xl border border-white/40 bg-amber-50 p-6 text-slate-900 shadow-2xl backdrop-blur-xl md:p-14 md:px-14 md:py-8 lg:py-14 dark:border-gray-700/50 dark:bg-gray-900/50 dark:text-white"
      >
        <!-- Header & Progress Bar -->
        <header
          v-if="stepConfig[currentStep].type === 'question'"
          class="mb-10 w-full"
        >
          <div class="mb-3 flex items-center justify-between">
            <span
              class="font-label-md text-label-md tracking-wider uppercase opacity-80"
            >
              Step {{ currentStep }} of {{ totalQuestions }}
            </span>
            <span class="font-label-md text-label-md text-primary font-bold">
              {{ progressPercentage }}%
            </span>
          </div>
          <UProgress v-model="progressPercentage" :max="100" color="primary" />
        </header>

        <!-- Dynamic Step Components -->
        <div class="min-h-[40dvh] lg:min-h-[50dvh]">
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
        </div>
      </div>
    </main>

    <!-- Navbar -->
    <div
      class="fixed bottom-0 left-0 z-50 w-full shrink-0 border-t border-white/30 bg-white/40 shadow-[0_-10px_40px_rgba(0,0,0,0.1)] backdrop-blur-xl dark:border-gray-800/50 dark:bg-gray-900/50"
    >
      <nav
        class="px-gutter max-w-container-max mx-auto flex h-16 items-center justify-between py-3 md:h-20"
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
