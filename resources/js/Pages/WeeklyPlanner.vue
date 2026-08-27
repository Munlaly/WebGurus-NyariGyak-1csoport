<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import PlannerDayColumn from '../Components/WeeklyPlanner/PlannerDayColumn.vue';

interface PlannerMeal {
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

interface DayPlan {
  total_calories: number;
  has_snack: boolean;
  perfect_match: boolean;
  meals: PlannerMeal[];
}

const props = defineProps<{
  initialPlan?: Record<string, DayPlan>;
}>();

// State
const weeklyPlan = ref<Record<string, DayPlan>>(props.initialPlan || {});
const daysOfWeek = computed(() => Object.keys(weeklyPlan.value));
const activeDay = ref<string>('');
const isSaving = ref(false);

onMounted(() => {
  if (daysOfWeek.value.length > 0) {
    activeDay.value = daysOfWeek.value[0];
  } else {
    fetchInitialPlan();
  }
});

const setActiveDay = (day: string) => {
  activeDay.value = day;
};

const fetchInitialPlan = async () => {
  try {
    const response = await axios.post('/meal-plan/generate');
    weeklyPlan.value = response.data.plan;
    if (Object.keys(response.data.plan).length > 0) {
      activeDay.value = Object.keys(response.data.plan)[0];
    }
  } catch (error) {
    console.error('Failed to fetch plan:', error);
    // TODO: Handle 400 Strict Filter errors
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

  targetMeal.isRolling = true;

  try {
    const response = await axios.post('/meal-plan/regenerate-meal', {
      meal_type: mealType,
    });
    const newRecipe = response.data.recipe;

    day.meals[mealIndex] = {
      ...newRecipe,
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
  // Fire parallel requests for all unpinned meals
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

const acceptAndFinalize = () => {
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

  const form = useForm({ plan: payload });
  form.post('/meal-plan/save', {
    onFinish: () => {
      isSaving.value = false;
    },
  });
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex h-full flex-col">
      <!-- 1. Sticky Action Bar -->
      <div
        class="bg-background/90 border-outline-variant/30 sticky top-0 z-40 -mx-4 mb-6 flex flex-col gap-4 border-b px-4 pt-2 pb-4 backdrop-blur-md md:-mx-8 md:flex-row md:items-center md:justify-between md:px-8"
      >
        <div>
          <h1 class="font-headline-md text-on-surface text-2xl font-bold">
            Review Generated Menu
          </h1>
          <p class="text-on-surface-variant text-sm">
            Pin your favorites, mark the rest for recalculation.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <button
            disabled
            class="bg-surface-container-low text-on-surface-variant flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold opacity-60 md:flex-none"
          >
            <span class="material-symbols-outlined text-[20px]">lock</span>
            Shopping List
          </button>

          <button
            class="text-primary hover:bg-primary-50 border-primary flex flex-1 items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-colors md:flex-none"
            @click="regenerateUnpinned"
          >
            <span class="material-symbols-outlined text-[20px]">sync</span>
            Regenerate Unpinned
          </button>

          <button
            :disabled="isSaving"
            class="bg-primary text-on-primary hover:bg-primary-600 flex flex-1 items-center justify-center gap-2 rounded-xl px-6 py-2.5 text-sm font-semibold shadow-sm transition-colors disabled:opacity-70 md:flex-none"
            @click="acceptAndFinalize"
          >
            <span
              v-if="isSaving"
              class="material-symbols-outlined animate-spin text-[20px]"
              >progress_activity</span
            >
            <span v-else class="material-symbols-outlined text-[20px]"
              >check_circle</span
            >
            Accept & Finalize
          </button>
        </div>
      </div>

      <!-- 2. Mobile Day Navigation (Tabs) -->
      <div
        class="scrollbar-hide border-outline-variant/30 -mx-4 mb-6 flex overflow-x-auto border-b px-4 md:hidden"
      >
        <button
          v-for="day in daysOfWeek"
          :key="day"
          :class="[
            'border-b-2 px-4 py-3 text-sm font-bold whitespace-nowrap transition-colors',
            activeDay === day
              ? 'border-primary text-primary'
              : 'text-on-surface-variant hover:text-on-surface border-transparent',
          ]"
          @click="setActiveDay(day)"
        >
          {{ day }}
        </button>
      </div>

      <!-- 3. Planner Content Canvas -->
      <!-- Desktop: flex container scrolling horizontally. Mobile: standard block -->
      <div class="flex flex-1 md:overflow-x-auto md:pb-8">
        <div class="flex w-full flex-col gap-6 md:w-auto md:flex-row md:gap-8">
          <template v-for="(dayData, dayName) in weeklyPlan" :key="dayName">
            <!-- Render rules: Hide on mobile if not active. Always show on md+ -->
            <div
              :class="[
                'w-full shrink-0 md:block',
                activeDay === dayName ? 'block' : 'hidden',
              ]"
            >
              <PlannerDayColumn
                :day-name="String(dayName)"
                :total-calories="dayData.total_calories"
                :perfect-match="dayData.perfect_match"
                :meals="dayData.meals"
                @toggle-pin="togglePin"
                @reroll="rerollMeal"
              />
            </div>
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Hide scrollbar for the mobile tab navigation but allow scrolling */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
