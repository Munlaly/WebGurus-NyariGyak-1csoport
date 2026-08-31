<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import PlannerDayColumn from '../Components/WeeklyPlanner/PlannerDayColumn.vue';
import { DayPlan, MealType, PlannerMeal } from '../Types/plannerInterfaces.js';

const props = defineProps<{
  initialPlan?: Record<string, DayPlan>;
}>();

// State
const weeklyPlan = ref<Record<string, DayPlan>>(props.initialPlan || {});
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

const activeDay = ref<string>('');
const isSaving = ref(false);
const isAlreadySaved = ref(false);

const saveButtonText = computed(() =>
  isAlreadySaved.value ? 'Update Plan' : 'Accept & Finalize',
);

const saveButtonIcon = computed(() =>
  isAlreadySaved.value ? 'update' : 'check_circle',
);

onMounted(() => {
  //Attempt to load saved state from session storage
  const savedState = sessionStorage.getItem(STORAGE_KEY);
  if (savedState) {
    try {
      const parsed = JSON.parse(savedState);
      if (parsed.weeklyPlan) weeklyPlan.value = parsed.weeklyPlan;
      if (parsed.activeDay) activeDay.value = parsed.activeDay;
      if (parsed.isAlreadySaved !== undefined)
        isAlreadySaved.value = parsed.isAlreadySaved;
    } catch (e) {
      console.error('Failed to load planner state', e);
    }
  }

  // If no active day was loaded, default to today
  if (!activeDay.value) {
    const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
    const isValidDay = allDays.find((d) => d.full === today);
    activeDay.value = isValidDay ? today : 'Monday';
  }

  //If no plan was loaded, generate a new one
  if (Object.keys(weeklyPlan.value).length === 0) {
    fetchInitialPlan();
  }
});

// Watch for changes to the plan or the active tab and save them
watch(
  () => ({
    weeklyPlan: weeklyPlan.value,
    activeDay: activeDay.value,
    isAlreadySaved: isAlreadySaved.value,
  }),
  (newState) => {
    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(newState));
  },
  { deep: true },
);

const setActiveDay = (day: string) => {
  activeDay.value = day;
};

// Extracted dynamic class logic
const getTabClass = (dayName: string) => {
  const baseClass =
    'border-b-2 px-3 py-3 text-sm font-bold whitespace-nowrap transition-colors sm:px-4 md:px-6';
  const activeClass = 'border-primary text-primary';
  const inactiveClass =
    'border-transparent text-on-surface-variant hover:text-on-surface';

  return `${baseClass} ${activeDay.value === dayName ? activeClass : inactiveClass}`;
};

const fetchInitialPlan = async () => {
  try {
    const response = await axios.post(route('meal-plan.generate'));
    const newPlan = response.data.plan;

    const types = [
      MealType.Breakfast,
      MealType.Lunch,
      MealType.Dinner,
      MealType.Snack,
    ];

    for (const day in newPlan) {
      newPlan[day].meals.forEach((meal: PlannerMeal, index: number) => {
        meal.meal_type = types[index] || MealType.Snack;
      });
    }

    weeklyPlan.value = newPlan;
  } catch (error) {
    console.error('Failed to fetch plan:', error);
  }
};

const togglePin = (dayName: string, mealId: number) => {
  const day = weeklyPlan.value[dayName];
  if (!day) return;
  const meal = day.meals.find((m) => m.id === mealId);
  if (meal) {
    meal.isPinned = !meal.isPinned;
  }
};

const rerollMeal = async (
  dayName: string,
  mealId: number,
  mealType: string,
) => {
  const day = weeklyPlan.value[dayName];
  if (!day) return;

  const mealIndex = day.meals.findIndex((m) => m.id === mealId);
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

    day.total_calories = day.meals.reduce(
      (sum, m) => sum + Number(m.calories),
      0,
    );
  } catch (error) {
    console.error('Failed to reroll meal:', error);
    targetMeal.isRolling = false;
  }
};

const regenerateUnpinned = async () => {
  const promises: Promise<void>[] = [];

  for (const [dayName, dayData] of Object.entries(weeklyPlan.value)) {
    for (const meal of dayData.meals) {
      if (!meal.isPinned && !meal.isRolling) {
        promises.push(rerollMeal(dayName, meal.id, meal.meal_type));
      }
    }
  }

  await Promise.allSettled(promises);
};

const toast = useToast();

const acceptAndFinalize = async () => {
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

      // Fire a success toast
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

    // Fire an error toast
    toast.add({
      title: 'Error',
      description: 'Failed to save the meal plan. Please try again.',
      color: 'error',
      icon: 'i-heroicons-x-circle',
    });
  } finally {
    isSaving.value = false;
  }
};
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
            class="text-primary hover:bg-primary-50 border-primary flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-colors sm:flex-1 md:w-auto"
            @click="regenerateUnpinned"
          >
            <span class="material-symbols-outlined text-[18px]">sync</span>
            Regenerate Unpinned
          </button>

          <button
            :disabled="isSaving"
            class="bg-primary text-on-primary hover:bg-primary-600 flex w-full items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold shadow-sm transition-colors disabled:opacity-70 sm:w-full md:w-auto"
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

      <!-- 2. Full Week Navigation (Tabs) - Horizontal Scrolling -->
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
  </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
