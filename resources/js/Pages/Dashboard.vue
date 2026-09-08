<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import MealCard from '../Components/MealCard.vue';
import axios from 'axios';
import { Meal, SearchResult } from '../Types/dashboardInterfaces.js';
import { useUnits } from '../Composables/useUnits.js';

const props = defineProps<{
  mealsByOffset: Record<string, Meal[]>;
  hasActivePlan: boolean;
}>();

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const { formatInputAmount } = useUnits();

const dayOffset = ref<number>(0);
const searchQuery = ref('');
const searchResults = ref<SearchResult[]>([]);
const isSearching = ref(false);
const showMealTypeModal = ref(false);
const selectedSearchResult = ref<SearchResult | null>(null);
const localPreparedStatus = ref<Record<number, boolean>>({});
const localFavoriteStatus = ref<Record<number, boolean>>({});

const showConfirmationModal = ref(false);
const showMismatchResolutionStep = ref(false);
const mismatchInputs = ref<Record<number, number>>({});
const confirmationData = ref<{
  mealPlanId: number | null;
  recipeId: number | null;
  message: string;
  missing: Array<{
    ingredient: string;
    required: number;
    available: number;
    unit: string;
    user_unit: string;
  }>;
  mismatched: Array<{
    id: number;
    ingredient: string;
    recipe_amount: number;
    recipe_unit: string;
    user_amount: number;
    user_unit: string;
  }>;
}>({
  mealPlanId: null,
  recipeId: null,
  message: '',
  missing: [],
  mismatched: [],
});

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
    isPrepared: localPreparedStatus.value[meal.meal_plan_id] ?? meal.isPrepared,
    isFavorite:
      localFavoriteStatus.value[meal.id] ?? (meal.isFavorite || false),
  }));
});

function handleRecipeSelection(recipe: SearchResult) {
  if (!recipe.meal_types || recipe.meal_types.length === 1) {
    // Only one type, swap immediately
    const type = recipe.meal_types?.[0] || 'dinner';
    executeSwap(recipe.id, type);
  } else {
    // Multiple types, ask the user
    selectedSearchResult.value = recipe;
    showMealTypeModal.value = true;
  }
}

function executeSwap(recipeId: number, mealType: string) {
  router.post(
    '/dashboard/swap-meal',
    {
      recipe_id: recipeId,
      meal_type: mealType,
      date_offset: dayOffset.value,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        searchQuery.value = '';
        searchResults.value = [];
        showMealTypeModal.value = false;
        selectedSearchResult.value = null;
      },
    },
  );
}
function getFormattedDate(offset: number) {
  const date = new Date();
  date.setDate(date.getDate() + offset);
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function goPrevDay() {
  if (dayOffset.value > -1) dayOffset.value--;
}

function goNextDay() {
  if (dayOffset.value < 1) dayOffset.value++;
}

async function toggleFavoriteStatus(id: number) {
  const meal = currentMeals.value.find((m) => m.id == id);
  if (!meal) return;

  const current = meal.isFavorite ?? false;
  localFavoriteStatus.value[id] = !current;

  try {
    await axios.post(`/recipe/${id}/favorite`);
  } catch (error) {
    console.error('Failed to save favorite: ', error);
    localFavoriteStatus.value[id] = current;
  }
}

function proceedToCook() {
  if (confirmationData.value.mismatched.length > 0) {
    showMismatchResolutionStep.value = true;
    confirmationData.value.mismatched.forEach((m) => {
      mismatchInputs.value[m.id] = 0; // default input to 0
    });
  } else {
    submitFinalCook();
  }
}

function submitFinalCook() {
  if (confirmationData.value.mealPlanId && confirmationData.value.recipeId) {
    handleCookMeal(
      confirmationData.value.mealPlanId,
      confirmationData.value.recipeId,
      true,
      mismatchInputs.value,
    );
  }
}

function cancelCooking() {
  showConfirmationModal.value = false;
  showMismatchResolutionStep.value = false;
  mismatchInputs.value = {};
}

async function handleCookMeal(
  mealPlanId: number,
  recipeId: number,
  confirmed = false,
  mismatchOverrides: Record<number, number> = {},
) {
  const meal = currentMeals.value.find((m) => m.meal_plan_id === mealPlanId);
  if (meal && meal.isPrepared) {
    return;
  }

  try {
    const response = await axios.post(`/recipe/${recipeId}/cook`, {
      meal_plan_id: mealPlanId,
      confirmed,
      mismatch_overrides: mismatchOverrides,
    });

    if (response.data.requires_confirmation) {
      confirmationData.value = {
        mealPlanId,
        recipeId,
        message: response.data.message,
        missing: response.data.summary.missing || [],
        mismatched: response.data.summary.mismatched || [],
      };
      showConfirmationModal.value = true;
      showMismatchResolutionStep.value = false;
      return;
    }
    if (response.data.success) {
      localPreparedStatus.value[mealPlanId] = true;
      cancelCooking();
      router.reload({ only: ['topbarData'] });
    }
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      console.error(
        'Failed to cook meal:',
        error.response?.data || error.message,
      );
      alert(
        error.response?.data?.message ||
          'An error occured while cooking the meal.',
      );
    } else {
      console.error('An unexpected error has occured: ', error);
      alert('An unexpected error has occured while cooking this meal.');
    }
  }
}

async function handleAddToCart(recipeId: number) {
  try {
    const response = await axios.post(`/recipe/${recipeId}/shopping-list`);
    alert(response.data.message);
  } catch (error) {
    console.error('Failed to add to shopping list:', error);
    alert('An error occurred while analyzing your inventory.');
  }
}

function goToPlanner() {
  router.visit(route('meal-plan.index'));
}

watch(searchQuery, (newVal) => {
  if (searchTimeout) clearTimeout(searchTimeout);

  if (newVal.length < 3) {
    searchResults.value = [];
    return;
  }

  isSearching.value = true;
  searchTimeout = setTimeout(async () => {
    try {
      const { data } = await axios.get(`/dashboard/search-recipes?q=${newVal}`);
      searchResults.value = data;
    } catch (error) {
      console.error('Search failed:', error);
    } finally {
      isSearching.value = false;
    }
  }, 300);
});
</script>

<template>
  <AuthenticatedLayout>
    <div class="animate-fade-in flex flex-1 flex-col gap-8">
      <!-- Date Picker -->
      <div
        v-if="props.hasActivePlan"
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

      <!-- Search Bar -->
      <div
        v-if="props.hasActivePlan"
        class="relative mx-auto -mt-4 w-full max-w-2xl"
      >
        <div class="relative">
          <span
            class="material-symbols-outlined text-on-surface-variant absolute top-1/2 left-4 -translate-y-1/2"
            >search</span
          >
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search for a recipe to add to this day..."
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border py-3 pr-10 pl-12 shadow-[0px_4px_20px_rgba(0,0,0,0.04)] outline-none focus:ring-1"
          />
          <span
            v-if="isSearching"
            class="material-symbols-outlined text-primary absolute top-1/2 right-4 -translate-y-1/2 animate-spin"
            >sync</span
          >
        </div>

        <!-- Search Dropdown with Single-Type Hint Badge -->
        <div
          v-if="searchResults.length > 0 && searchQuery.length > 2"
          class="bg-surface-container-lowest border-outline-variant absolute z-40 mt-2 w-full overflow-hidden rounded-xl border shadow-lg"
        >
          <ul class="max-h-64 overflow-y-auto">
            <li
              v-for="res in searchResults"
              :key="res.id"
              class="hover:bg-surface-container-low border-outline-variant/50 flex cursor-pointer items-center justify-between border-b px-4 py-3 transition-colors last:border-0"
              @click="handleRecipeSelection(res)"
            >
              <span class="text-on-surface truncate pr-4 font-medium">{{
                res.name
              }}</span>
              <div class="flex shrink-0 items-center gap-2">
                <span
                  v-if="res.meal_types && res.meal_types.length === 1"
                  class="bg-primary/10 text-primary rounded-md px-2 py-1 text-[10px] font-bold tracking-wider uppercase"
                >
                  Replaces {{ res.meal_types[0] }}
                </span>
                <span
                  class="text-on-surface-variant bg-surface-container rounded-md px-2 py-1 text-xs whitespace-nowrap"
                  >{{ res.calories }} kcal</span
                >
              </div>
            </li>
          </ul>
        </div>
      </div>

      <!-- Meal Grid -->
      <div
        v-if="props.hasActivePlan"
        class="grid flex-1 grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
      >
        <MealCard
          v-for="meal in currentMeals"
          :id="meal.id"
          :key="meal.meal_plan_id"
          :title="meal.title"
          :calories="meal.calories"
          :prep-time="meal.prepTime"
          :image-url="meal.imageUrl"
          :image-alt="meal.imageAlt"
          :is-prepared="meal.isPrepared"
          :is-favorite="meal.isFavorite"
          @toggle-eaten="handleCookMeal(meal.meal_plan_id, meal.id, false)"
          @toggle-favorite="toggleFavoriteStatus(meal.id)"
          @add-to-cart="handleAddToCart(meal.id)"
        />
      </div>

      <!-- Empty State for No Plan -->
      <div v-else class="flex max-h-fit flex-1 items-start justify-center">
        <UEmpty
          icon="i-heroicons-calendar"
          title="No weekly plan yet"
          description="It looks like you haven't generated a meal plan for this week. Let's get you set up."
          :actions="[{ label: 'Go to Weekly Planner', onClick: goToPlanner }]"
          class="border-error w-full border-2 border-dashed"
        />
      </div>

      <!-- Confirmation / Warning Modal -->
      <div
        v-if="showConfirmationModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div
          class="bg-surface-container-lowest w-full max-w-lg rounded-2xl p-6 shadow-xl"
        >
          <!-- STEP 1: The Warnings -->
          <div v-if="!showMismatchResolutionStep">
            <h3 class="text-headline-md text-on-surface mb-2 font-bold">
              Inventory Warning
            </h3>
            <p class="text-body-md text-on-surface-variant mb-4">
              {{ confirmationData.message }}
            </p>

            <div v-if="confirmationData.missing.length > 0" class="mb-4">
              <h4 class="text-error mb-1 font-semibold">
                Missing Ingredients:
              </h4>
              <ul class="text-on-surface-variant list-disc pl-5 text-sm">
                <li v-for="(item, idx) in confirmationData.missing" :key="idx">
                  <span class="font-medium capitalize">{{
                    item.ingredient
                  }}</span>
                  — Required:
                  {{ formatInputAmount(item.required, item.unit).amount }}
                  {{ formatInputAmount(item.required, item.unit).unit }},
                  Available:
                  {{ formatInputAmount(item.available, item.unit).amount }}
                  {{ formatInputAmount(item.available, item.unit).unit }}
                </li>
              </ul>
            </div>

            <div v-if="confirmationData.mismatched.length > 0" class="mb-6">
              <h4 class="text-tertiary mb-1 font-semibold">Unit Mismatches:</h4>
              <ul class="text-on-surface-variant list-disc pl-5 text-sm">
                <li
                  v-for="(item, idx) in confirmationData.mismatched"
                  :key="idx"
                >
                  <span class="font-medium capitalize">{{
                    item.ingredient
                  }}</span>
                  — Recipe requires
                  {{
                    formatInputAmount(item.recipe_amount, item.recipe_unit)
                      .amount
                  }}
                  {{
                    formatInputAmount(item.recipe_amount, item.recipe_unit)
                      .unit
                  }}, but inventory has
                  {{
                    formatInputAmount(item.user_amount, item.user_unit).amount
                  }}
                  {{ formatInputAmount(item.user_amount, item.user_unit).unit }}
                </li>
              </ul>
            </div>

            <div class="flex justify-end gap-3">
              <button
                class="border-outline-variant text-on-surface hover:bg-surface-container-low rounded-lg border px-4 py-2"
                @click="cancelCooking"
              >
                Cancel
              </button>
              <button
                class="bg-primary text-on-primary hover:bg-primary/90 rounded-lg px-4 py-2"
                @click="proceedToCook"
              >
                Cook Anyway
              </button>
            </div>
          </div>

          <!-- STEP 2: The Mismatch Resolution Inputs -->
          <div v-else class="animate-fade-in">
            <h3 class="text-headline-md text-on-surface mb-2 font-bold">
              Resolve Mismatches
            </h3>
            <p class="text-body-md text-on-surface-variant mb-6">
              We can't automatically subtract these mismatched units. Please
              enter how much of each ingredient you have left after cooking.
            </p>

            <div class="mb-6 flex max-h-64 flex-col gap-4 overflow-y-auto pr-2">
              <div
                v-for="item in confirmationData.mismatched"
                :key="item.id"
                class="bg-surface-container-low rounded-xl p-4"
              >
                <p class="text-on-surface mb-1 font-medium">
                  {{ item.ingredient }}
                </p>
                <p class="text-on-surface-variant mb-3 text-xs">
                  Started with:
                  {{
                    formatInputAmount(item.user_amount, item.user_unit).amount
                  }}
                  {{ formatInputAmount(item.user_amount, item.user_unit).unit }}
                  | Recipe needed:
                  {{
                    formatInputAmount(item.recipe_amount, item.recipe_unit)
                      .amount
                  }}
                  {{
                    formatInputAmount(item.recipe_amount, item.recipe_unit).unit
                  }}
                </p>

                <div class="flex items-center gap-3">
                  <input
                    v-model="mismatchInputs[item.id]"
                    type="number"
                    step="0.01"
                    min="0"
                    class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-lg border px-3 py-2 outline-none focus:ring-1"
                    placeholder="Amount left..."
                  />
                  <span
                    class="text-on-surface-variant text-sm font-medium whitespace-nowrap"
                    >{{ item.user_unit }} left</span
                  >
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-3">
              <button
                class="text-on-surface-variant hover:bg-surface-container-low rounded-lg px-4 py-2"
                @click="showMismatchResolutionStep = false"
              >
                Back
              </button>
              <button
                class="bg-primary text-on-primary hover:bg-primary/90 rounded-lg px-4 py-2"
                @click="submitFinalCook"
              >
                Confirm & Cook
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Swap Modal -->
      <div
        v-if="showMealTypeModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
      >
        <div
          class="bg-surface-container-lowest w-full max-w-sm rounded-2xl p-6 text-center shadow-xl"
        >
          <h3 class="text-headline-md text-on-surface mb-2 font-bold">
            Select Meal Type
          </h3>
          <p class="text-body-md text-on-surface-variant mb-6">
            Where would you like to slot
            <strong>{{ selectedSearchResult?.name }}</strong
            >?
          </p>
          <div class="flex flex-col gap-3">
            <button
              v-for="type in selectedSearchResult?.meal_types"
              :key="type"
              class="border-outline-variant text-on-surface hover:bg-primary hover:text-on-primary hover:border-primary w-full rounded-lg border py-3 font-semibold capitalize transition-colors active:scale-95"
              @click="executeSwap(selectedSearchResult!.id, type)"
            >
              Set as {{ type }}
            </button>
          </div>
          <button
            class="text-on-surface-variant hover:text-on-surface mt-6 w-full text-sm underline transition-colors"
            @click="showMealTypeModal = false"
          >
            Cancel
          </button>
        </div>
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
