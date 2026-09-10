<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import PlannerDayColumn from '../Components/WeeklyPlanner/PlannerDayColumn.vue';
import ActionModal from '../Components/Modals/ActionModal.vue';
import { DayPlan, MealType, PlannerMeal } from '../Types/plannerInterfaces.js';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  initialPlan?: Record<string, DayPlan>;
  hasSavedPlan?: boolean;
}>();

const toast = useToast();

const STORAGE_KEY = 'weekly_planner_state';

const allDays = [
  { full: 'Monday', short: 'Mon' },
  { full: 'Tuesday', short: 'Tue' },
  { full: 'Wednesday', short: 'Wed' },
  { full: 'Thursday', short: 'Thu' },
  { full: 'Friday', short: 'Fri' },
  { full: 'Saturday', short: 'Sat' },
  { full: 'Sunday', short: 'Sun' },
];

const weeklyPlan = ref<Record<string, DayPlan>>(props.initialPlan || {});
const activeDay = ref<string>('');
const isSaving = ref(false);
const isAlreadySaved = ref(false);
const dailyCalorieTarget = ref<number | null>(null);
const isDeletePlanModalOpen = ref(false);

const saveButtonText = computed(() =>
  isAlreadySaved.value ? 'Update Plan' : 'Accept & Finalize',
);

const saveButtonIcon = computed(() =>
  isAlreadySaved.value ? 'update' : 'check_circle',
);

function setActiveDay(day: string) {
  activeDay.value = day;
}

function getTabClass(dayName: string) {
  const baseClass =
    'border-b-2 px-3 py-3 text-sm font-bold whitespace-nowrap transition-colors sm:px-4 md:px-6';
  const activeClass = 'border-primary text-primary';
  const inactiveClass =
    'border-transparent text-on-surface-variant hover:text-on-surface';

  return `${baseClass} ${activeDay.value === dayName ? activeClass : inactiveClass}`;
}

function togglePin(dayName: string, mealId: number, mealType: string) {
  const day = weeklyPlan.value[dayName];
  if (!day) return;
  const meal = day.meals.find(
    (m) => m.id === mealId && m.meal_type === mealType,
  );
  if (meal) {
    meal.isPinned = !meal.isPinned;
  }
}

function recomputeDayMatch(day: DayPlan) {
  day.total_calories = day.meals.reduce(
    (sum, m) => sum + Number(m.calories || 0),
    0,
  );
  const target = day.target_calories || dailyCalorieTarget.value;

  if (target) {
    const min = target * 0.9;
    const max = target * 1.1;
    day.perfect_match = day.total_calories >= min && day.total_calories <= max;
  }
}

async function fetchInitialPlan() {
  try {
    const response = await axios.post(route('meal-plan.generate'));
    weeklyPlan.value = response.data.plan;
    dailyCalorieTarget.value = response.data.target_calories;
  } catch (error) {
    console.error('Failed to fetch plan:', error);
  }
}

async function rerollMeal(dayName: string, mealId: number, mealType: string) {
  const day = weeklyPlan.value[dayName];
  if (!day) return;

  const mealIndex = day.meals.findIndex(
    (m) => m.id === mealId && m.meal_type === mealType,
  );
  if (mealIndex === -1) return;

  const targetMeal = day.meals[mealIndex];
  if (targetMeal.isPinned) return;

  const actualMealType =
    mealType ||
    [MealType.Breakfast, MealType.Lunch, MealType.Dinner, MealType.Snack][
      mealIndex
    ];

  targetMeal.isRolling = true;
  try {
    const response = await axios.post(route('meal-plan.regenerate-meal'), {
      meal_type: actualMealType,
    });
    const newRecipe = response.data.recipe;

    day.meals[mealIndex] = {
      ...newRecipe,
      meal_type: actualMealType,
      isPinned: false,
      isRolling: false,
    };
    recomputeDayMatch(day);
  } catch (error) {
    console.error('Failed to reroll meal:', error);
    targetMeal.isRolling = false;
  }
}

async function regenerateUnpinned() {
  for (const day in weeklyPlan.value) {
    weeklyPlan.value[day].meals.forEach((meal) => {
      if (!meal.isPinned) {
        meal.isRolling = true;
      }
    });
  }

  try {
    const response = await axios.post(route('meal-plan.generate'));
    const freshPlan = response.data.plan;
    dailyCalorieTarget.value = response.data.target_calories;

    for (const dayName in freshPlan) {
      if (weeklyPlan.value[dayName]) {
        const existingMeals = weeklyPlan.value[dayName].meals;
        const newDayMeals: PlannerMeal[] = [];
        let hasSnack = false;

        const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];

        mealTypes.forEach((type) => {
          const pinnedOld = existingMeals.find(
            (m) => m.meal_type === type && m.isPinned,
          );

          if (pinnedOld) {
            newDayMeals.push(pinnedOld);
            if (type === 'snack') hasSnack = true;
          } else {
            const freshNew = freshPlan[dayName].meals.find(
              (m: PlannerMeal) => m.meal_type === type,
            );

            if (freshNew) {
              newDayMeals.push(freshNew);
              if (type === 'snack') hasSnack = true;
            }
          }
        });
        freshPlan[dayName].meals = newDayMeals;
        freshPlan[dayName].has_snack = hasSnack;
      }
    }

    weeklyPlan.value = freshPlan;
    for (const dayName in weeklyPlan.value) {
      recomputeDayMatch(weeklyPlan.value[dayName]);
    }

    toast.add({
      title: 'Menu Regenerated',
      description: 'Unpinned meals refreshed for the upcoming week!',
      color: 'success',
      icon: 'i-heroicons-arrow-path',
    });
  } catch (error: unknown) {
    console.error('Failed to regenerate plan:', error);

    for (const day in weeklyPlan.value) {
      weeklyPlan.value[day].meals.forEach((meal) => {
        meal.isRolling = false;
      });
    }

    let backendMessage =
      'Failed to regenerate the meal plan. Please try again.';
    if (axios.isAxiosError(error)) {
      backendMessage = error.response?.data?.message || backendMessage;
    }
    toast.add({
      title: 'Error',
      description: backendMessage,
      color: 'error',
      icon: 'i-heroicons-x-circle',
    });
  }
}

async function executeDeletePlan() {
  try {
    const response = await axios.delete(route('meal-plan.destroy'));
    if (response.data.success) {
      weeklyPlan.value = {};
      isAlreadySaved.value = false;
      isDeletePlanModalOpen.value = false;
      sessionStorage.removeItem(STORAGE_KEY);

      toast.add({
        title: 'Plan Reset',
        description: 'Weekly plan cleared. Generating a fresh plan...',
        color: 'success',
        icon: 'i-heroicons-trash',
      });

      fetchInitialPlan();
    }
  } catch (error) {
    console.error('Failed to delete plan:', error);
    isDeletePlanModalOpen.value = false;
    toast.add({
      title: 'Error',
      description: 'Failed to delete the plan. Please try again.',
      color: 'error',
      icon: 'i-heroicons-x-circle',
    });
  }
}

async function acceptAndFinalize() {
  isSaving.value = true;

  const payload: Record<
    string,
    { meals: { id: number; meal_type: string }[] }
  > = {};

  for (const [dayName, dayData] of Object.entries(weeklyPlan.value)) {
    payload[dayName] = {
      meals: dayData.meals.map((m) => ({ id: m.id, meal_type: m.meal_type })),
    };
  }

  try {
    const response = await axios.post(route('meal-plan.save'), {
      plan: payload,
    });

    if (response.data.success) {
      isAlreadySaved.value = true;

      router.reload({ only: ['topbarData'] });

      toast.add({
        title: 'Success!',
        description: isAlreadySaved.value
          ? 'Weekly plan updated successfully!'
          : response.data.message,
        color: 'success',
        icon: 'i-heroicons-check-circle',
      });
    }
  } catch (error) {
    console.error('Failed to save plan:', error);

    toast.add({
      title: 'Error',
      description: 'Failed to save the meal plan. Please try again.',
      color: 'error',
      icon: 'i-heroicons-x-circle',
    });
  } finally {
    isSaving.value = false;
  }
}

watch(
  () => ({
    weeklyPlan: weeklyPlan.value,
    activeDay: activeDay.value,
    isAlreadySaved: isAlreadySaved.value,
    dailyCalorieTarget: dailyCalorieTarget.value,
  }),
  (newState) => {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(newState));
  },
  { deep: true },
);

onMounted(() => {
  const savedState = sessionStorage.getItem(STORAGE_KEY);
  if (savedState) {
    try {
      const parsed = JSON.parse(savedState);
      if (parsed.weeklyPlan) weeklyPlan.value = parsed.weeklyPlan;
      if (parsed.activeDay) activeDay.value = parsed.activeDay;
      if (parsed.isAlreadySaved !== undefined)
        isAlreadySaved.value = parsed.isAlreadySaved;
      if (parsed.dailyCalorieTarget)
        dailyCalorieTarget.value = parsed.dailyCalorieTarget;
    } catch (e) {
      console.error('Failed to load planner state', e);
    }
  }

  if (!activeDay.value) {
    const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
    const isValidDay = allDays.find((d) => d.full === today);
    activeDay.value = isValidDay ? today : 'Monday';
  }

  if (Object.keys(weeklyPlan.value).length === 0 && !props.hasSavedPlan) {
    fetchInitialPlan();
  } else if (Object.keys(weeklyPlan.value).length === 0 && props.hasSavedPlan) {
    axios
      .get(route('meal-plan.current'))
      .then((response) => {
        if (response.data.success) {
          weeklyPlan.value = response.data.plan;
          isAlreadySaved.value = true;
          dailyCalorieTarget.value = response.data.target_calories ?? null;
        }
      })
      .catch((error) => {
        console.error('Failed to load existing plan:', error);
      });
  }
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex h-full w-full min-w-0 flex-col">
      <!-- 1. Sticky Action Bar -->
      <div
        class="bg-background/90 border-outline-variant/30 sticky top-0 z-40 mb-6 flex flex-col gap-4 border-b pt-2 pb-4 backdrop-blur-md md:flex-row md:items-center md:justify-between"
      >
        <div class="shrink-0">
          <h1
            class="font-headline-md text-on-surface text-xl font-bold md:text-2xl"
          >
            Review Generated Menu
          </h1>
          <p class="text-on-surface-variant text-xs md:text-sm">
            Pin your favorites, mark the rest for recalculation.
          </p>
        </div>

        <div
          class="flex w-full flex-col gap-2 sm:flex-row sm:flex-wrap md:w-auto md:gap-3"
        >
          <button
            disabled
            class="bg-surface-container-low text-on-surface-variant flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold opacity-60 sm:flex-1 md:w-auto"
          >
            <span class="material-symbols-outlined text-[18px]">lock</span>
            Shopping List
          </button>

          <button
            class="text-error hover:bg-error-50 border-error/50 flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-colors sm:flex-1 md:w-auto"
            @click="isDeletePlanModalOpen = true"
          >
            <span class="material-symbols-outlined text-[18px]"
              >delete_sweep</span
            >
            Delete Plan
          </button>

          <button
            class="border-primary text-primary hover:bg-primary/10 flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-colors sm:flex-1 md:w-auto"
            @click="regenerateUnpinned"
          >
            <span class="material-symbols-outlined text-[18px]">sync</span>
            Regenerate Unpinned
          </button>

          <button
            :disabled="isSaving"
            class="bg-primary text-on-primary hover:bg-primary/90 flex w-full items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold shadow-sm transition-colors disabled:opacity-70 sm:w-full md:w-auto dark:hover:bg-[#b080ea]"
            @click="acceptAndFinalize"
          >
            <span
              v-if="isSaving"
              class="material-symbols-outlined animate-spin text-[18px]"
              >progress_activity</span
            >
            <span v-else class="material-symbols-outlined text-[18px]">{{
              saveButtonIcon
            }}</span>
            {{ saveButtonText }}
          </button>
        </div>
      </div>

      <!-- 2. Full Week Navigation (Tabs) -->
      <div
        class="scrollbar-hide border-outline-variant/30 mb-6 flex w-full overflow-x-auto border-b"
      >
        <button
          v-for="day in allDays"
          :key="day.full"
          :class="getTabClass(day.full)"
          @click="setActiveDay(day.full)"
        >
          <span class="hidden sm:inline">{{ day.full }}</span>
          <span class="sm:hidden">{{ day.short }}</span>
        </button>
      </div>

      <!-- 3. Planner Content Canvas -->
      <div class="flex w-full flex-1 flex-col pb-8">
        <template v-if="weeklyPlan[activeDay]">
          <PlannerDayColumn
            :day-name="activeDay"
            :total-calories="weeklyPlan[activeDay].total_calories"
            :perfect-match="weeklyPlan[activeDay].perfect_match"
            :meals="weeklyPlan[activeDay].meals"
            @toggle-pin="togglePin"
            @reroll="rerollMeal"
          />
        </template>

        <!-- Empty State -->
        <div
          v-else
          class="border-outline-variant/50 text-on-surface-variant flex flex-1 items-center justify-center rounded-2xl border border-dashed p-8 sm:p-12"
        >
          <div class="flex flex-col items-center gap-3 text-center">
            <span
              class="material-symbols-outlined text-4xl opacity-40 sm:text-5xl"
              >event_busy</span
            >
            <p class="text-base font-semibold sm:text-lg">
              No meals planned for {{ activeDay }}
            </p>
            <p class="text-xs opacity-70 sm:text-sm">
              Your algorithm generated plans starting from today onwards.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Weekly Plan Confirmation Modal -->
    <ActionModal
      :show="isDeletePlanModalOpen"
      title="Delete Weekly Plan"
      submit-text="Delete Plan"
      submit-variant="error"
      @close="isDeletePlanModalOpen = false"
      @submit="executeDeletePlan"
    >
      <p class="font-body-md text-on-surface-variant">
        Are you sure you want to delete your entire weekly plan and start over?
      </p>
    </ActionModal>
  </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
PlannerDayColumn .scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
