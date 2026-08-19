<script setup lang="ts">
import { ref, computed } from 'vue';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import MealCard from '../Components/MealCard.vue';

interface Meal {
  id: number;
  title: string;
  calories: number;
  prepTime: number;
  imageUrl: string;
  imageAlt: string;
  isPrepared: boolean;
}

const props = defineProps<{
  mealsByOffset: Record<string, Meal[]>;
}>();

const dayOffset = ref<number>(0);

// Local state tracking for toggle actions across days
const localPreparedStatus = ref<Record<number, boolean>>({});

const getFormattedDate = (offset: number) => {
  const date = new Date();
  date.setDate(date.getDate() + offset);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const activeDateLabel = computed(() => {
  if (dayOffset.value === -1) return `Yesterday (${getFormattedDate(-1)})`;
  if (dayOffset.value === 1) return `Tomorrow (${getFormattedDate(1)})`;
  return `Today (${getFormattedDate(0)})`;
});

const prevDateLabel = computed(() => {
  if (dayOffset.value === 0) return 'Yesterday';
  if (dayOffset.value === 1) return 'Today';
  return '';
});

const nextDateLabel = computed(() => {
  if (dayOffset.value === 0) return 'Tomorrow';
  if (dayOffset.value === -1) return 'Today';
  return '';
});

const goPrevDay = () => {
  if (dayOffset.value > -1) dayOffset.value--;
};

const goNextDay = () => {
  if (dayOffset.value < 1) dayOffset.value++;
};

const leftChevronClasses = computed(() =>
  dayOffset.value === -1
    ? 'text-outline-variant cursor-not-allowed opacity-30'
    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary',
);

const rightChevronClasses = computed(() =>
  dayOffset.value === 1
    ? 'text-outline-variant cursor-not-allowed opacity-30'
    : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary',
);

const currentMeals = computed(() => {
  const list = props.mealsByOffset[String(dayOffset.value)] || [];
  return list.map((meal) => ({
    ...meal,
    isPrepared: localPreparedStatus.value[meal.id] ?? meal.isPrepared,
  }));
});

const toggleMealStatus = (id: number) => {
  const current = localPreparedStatus.value[id] ?? false;
  localPreparedStatus.value[id] = !current;
};
</script>

<template>
  <AuthenticatedLayout>
    <div class="animate-fade-in flex flex-1 flex-col gap-8">
      <!-- Date Picker -->
      <div
        class="bg-surface-container-lowest mx-auto flex w-full max-w-md items-center justify-between rounded-xl p-4 shadow-[0px_4px_20px_rgba(0,0,0,0.04)]"
      >
        <button :class="leftChevronClasses" @click="goPrevDay">
          <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <div class="font-headline-md text-headline-md flex items-center gap-6">
          <span
            class="text-on-surface-variant font-body-lg text-body-lg hidden opacity-50 sm:inline"
          >
            {{ prevDateLabel }}
          </span>
          <span class="text-primary border-primary border-b-2 pb-1 font-bold">
            {{ activeDateLabel }}
          </span>
          <span
            class="text-on-surface-variant font-body-lg text-body-lg hidden opacity-50 sm:inline"
          >
            {{ nextDateLabel }}
          </span>
        </div>
        <button :class="rightChevronClasses" @click="goNextDay">
          <span class="material-symbols-outlined">chevron_right</span>
        </button>
      </div>

      <!-- Meal Grid -->
      <div class="grid flex-1 grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <MealCard
          v-for="meal in currentMeals"
          :id="meal.id"
          :key="meal.id"
          :title="meal.title"
          :calories="meal.calories"
          :prep-time="meal.prepTime"
          :image-url="meal.imageUrl"
          :image-alt="meal.imageAlt"
          :is-prepared="meal.isPrepared"
          @toggle-cooked="toggleMealStatus(meal.id)"
        />
      </div>

      <!-- Weekly Analytics Section -->
      <div
        class="bg-surface-container-lowest border-surface-container-high mt-auto rounded-xl border p-8 shadow-[0px_4px_20px_rgba(0,0,0,0.04)]"
      >
        <div class="mb-6 flex items-center justify-between">
          <h3 class="font-headline-lg text-headline-lg text-on-surface">
            Weekly Analytics
          </h3>
          <span class="material-symbols-outlined text-primary">monitoring</span>
        </div>
        <div
          class="bg-surface-container-low text-on-surface-variant border-outline-variant font-body-md text-body-md flex h-48 w-full items-center justify-center rounded-lg border border-dashed"
        >
          <div class="flex flex-col items-center gap-2">
            <span
              class="material-symbols-outlined text-tertiary-container text-4xl"
            >
              bar_chart
            </span>
            <span>Analytics visualization will appear here</span>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
