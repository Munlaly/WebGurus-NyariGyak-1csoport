<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
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
  baseDietIds: number[];
}
const page = usePage<QuizPageProps>();

const dislikedIngredientsObjects = ref<{ id: number; label: string }[]>([]);

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

const STORAGE_KEY = 'meal_plan_quiz_progress';
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
const isDietStepValid = ref(true);

const username = computed(() => page.props.auth?.user?.username || 'Guest');
const dietaryOptions = computed(() => page.props.dietaryOptions || []);
const totalQuestions = computed(
  () => stepConfig.filter((s) => s.type === 'question').length,
);
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
  if (currentStep.value === 4 && !isDietStepValid.value) {
    return true;
  }

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
    form.post(route('quiz.store'), {
      onSuccess: () => {
        sessionStorage.removeItem(STORAGE_KEY);
      },
    });
  } else if (currentStep.value < stepConfig.length - 1) {
    currentStep.value++;
  }
}

watch(
  dislikedIngredientsObjects,
  (newSelection) => {
    form.disliked_ingredients = newSelection.map((item) => item.id);
  },
  { deep: true },
);

watch(
  () => ({
    form: form.data(),
    currentStep: currentStep.value,
    dislikedIngredientsObjects: dislikedIngredientsObjects.value,
  }),
  (newState) => {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(newState));
  },
  { deep: true },
);

// Load saved state
onMounted(() => {
  const saved = sessionStorage.getItem(STORAGE_KEY);
  if (saved) {
    try {
      const parsed = JSON.parse(saved);

      if (parsed.currentStep !== undefined) {
        currentStep.value = parsed.currentStep;
      }

      if (parsed.dislikedIngredientsObjects) {
        dislikedIngredientsObjects.value = parsed.dislikedIngredientsObjects;
      }

      if (parsed.form) {
        Object.assign(form, parsed.form);
      }
    } catch (e) {
      console.error('Failed to parse session storage for quiz', e);
      sessionStorage.removeItem(STORAGE_KEY);
    }
  }
});
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
        <div class="mb-2 flex w-full justify-end">
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="group flex items-center gap-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
          >
            <span class="underline-offset-4 group-hover:underline"
              >Log Out</span
            >
            <UIcon
              name="i-heroicons-arrow-right-on-rectangle"
              class="h-5 w-5 transition-transform group-hover:translate-x-0.5"
            />
          </Link>
        </div>
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
            :base-diet-ids="page.props.baseDietIds"
            @update:is-valid="isDietStepValid = $event"
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
        <footer>
          <nav
            class="mt-10 flex items-center justify-between border-t border-slate-200 pt-6"
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
        </footer>
      </div>
    </main>
  </UForm>
</template>
