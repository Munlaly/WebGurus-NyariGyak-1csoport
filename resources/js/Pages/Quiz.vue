<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  categories: {
    type: Array,
    // Falling back to the dummy data if no data is passed (will be removed later)
    default: () => [
      {
        id: 1,
        name: 'Vegetables',
        icon: 'eco',
        ingredients: [
          'Mushrooms',
          'Onions',
          'Tomatoes',
          'Broccoli',
          'Bell Peppers',
        ],
      },
      {
        id: 2,
        name: 'Dairy & Eggs',
        icon: 'water_drop',
        ingredients: ['Cheese', 'Milk', 'Eggs', 'Yogurt'],
      },
      {
        id: 3,
        name: 'Meat & Seafood',
        icon: 'set_meal',
        ingredients: ['Pork', 'Beef', 'Shrimp', 'Salmon', 'Chicken'],
      },
      {
        id: 4,
        name: 'Herbs & Spices',
        icon: 'nutrition',
        ingredients: ['Cilantro', 'Garlic', 'Oregano', 'Basil'],
      },
    ],
  },
});

const currentStep = ref(1);

const availableGoals = [
  { value: 'Lose weight', title: 'Lose weight', icon: 'monitor_weight' },
  {
    value: 'Gain weight (bulking)',
    title: 'Gain weight (bulking)',
    icon: 'fitness_center',
  },
  { value: 'Build muscle', title: 'Build muscle', icon: 'accessibility_new' },
  {
    value: 'Trying to eat healthy on a day to day basis',
    title: 'Trying to eat healthy on a day-to-day basis',
    icon: 'eco',
  },
  {
    value: 'Inspiration for my fridge',
    title: 'Inspiration for my fridge (ZeroWaste focus)',
    icon: 'kitchen',
    fullWidth: true,
  },
];

const availableDiets = [
  { name: 'Omnivore', icon: 'restaurant', desc: 'I eat everything' },
  { name: 'Vegetarian', icon: 'eco', desc: 'No meat or poultry' },
  { name: 'Vegan', icon: 'cruelty_free', desc: 'No animal products' },
  { name: 'Gluten-Free', icon: 'agriculture', desc: 'Avoid wheat & gluten' },
  { name: 'Dairy-Free', icon: 'water_drop', desc: 'No milk or cheese' },
  { name: 'Keto / Low-Carb', icon: 'scale', desc: 'High fat, low carb' },
  { name: 'Nut-Free', icon: 'block', desc: 'Allergy safe' },
];

const selectedCategory = ref(null);
const currentCustomInput = ref('');
const showCalorieWarning = ref(false);

const householdOptions = [
  {
    value: 'Just for myself (1 person)',
    title: 'Just for myself',
    desc: '1 person',
    icon: 'person',
  },
  {
    value: 'Me and my partner (2 people)',
    title: 'Me and my partner',
    desc: '2 people',
    icon: 'group',
  },
  {
    value: 'For the entire family (3-5 people)',
    title: 'For the entire family',
    desc: '3-5+ people',
    icon: 'diversity_3',
  },
];

const prepTimeOptions = [
  {
    value: 'Lightning fast: under 20 minutes',
    title: 'Lightning fast',
    desc: 'Under 20 minutes',
    icon: 'bolt',
  },
  {
    value: 'Normal pace: 30-45 minutes',
    title: 'Normal pace',
    desc: '30-45 minutes',
    icon: 'schedule',
  },
  {
    value: 'Leisurely/weekend: over 1 hour',
    title: 'Leisurely / Weekend',
    desc: 'Over 1 hour',
    icon: 'restaurant_menu',
  },
];

const form = useForm({
  goals: [],
  meal_plan_preferences: [],
  household_size: '',
  prep_time_preference: '',
  budget_or_comfort: '',
  daily_calorie_target: 2000,
  disliked_ingredients: [],
  custom_dislikes: [],
});

onMounted(() => {
  const savedProgress = JSON.parse(sessionStorage.getItem('zeroWasteQuiz'));
  if (savedProgress) {
    Object.assign(form, savedProgress);
  }
  watch(
    () => form.data(),
    (newState) => {
      sessionStorage.setItem('zeroWasteQuiz', JSON.stringify(newState));
    },
    { deep: true },
  );
});

function decreaseCalories() {
  if (form.daily_calorie_target <= 1300) {
    form.daily_calorie_target = 1300;
    showCalorieWarning.value = true;
  } else {
    form.daily_calorie_target -= 50;
    showCalorieWarning.value = false;
  }
}

function increaseCalories() {
  if (form.daily_calorie_target >= 4000) {
    form.daily_calorie_target = 4000;
    showCalorieWarning.value = true;
  } else {
    form.daily_calorie_target += 50;
    showCalorieWarning.value = false;
  }
}

function handleCalorieBlur() {
  if (form.daily_calorie_target < 1300) {
    form.daily_calorie_target = 1300;
  } else if (form.daily_calorie_target > 4000) {
    form.daily_calorie_target = 4000;
  }
  showCalorieWarning.value = false;
}

const isNextDisabled = computed(() => {
  if (
    currentStep.value === 4 &&
    (form.daily_calorie_target < 1300 || form.daily_calorie_target > 4000)
  )
    return true;
  if (currentStep.value === 5 && !form.budget_or_comfort) return true;
  if (currentStep.value === 6 && !form.household_size) return true;
  if (currentStep.value === 7 && !form.prep_time_preference) return true;
  return false;
});

function nextStep() {
  currentStep.value++;
}

function prevStep() {
  if (currentStep.value > 1) currentStep.value--;
}

function toggleGoal(goalValue) {
  const loseWeightVal = 'Lose weight';
  const gainWeightVal = 'Gain weight (bulking)';

  if (goalValue === loseWeightVal && form.goals.includes(gainWeightVal)) {
    const index = form.goals.indexOf(gainWeightVal);
    if (index !== -1) form.goals.splice(index, 1);
  } else if (
    goalValue === gainWeightVal &&
    form.goals.includes(loseWeightVal)
  ) {
    const index = form.goals.indexOf(loseWeightVal);
    if (index !== -1) form.goals.splice(index, 1);
  }

  const index = form.goals.indexOf(goalValue);
  if (index === -1) {
    form.goals.push(goalValue);
  } else {
    form.goals.splice(index, 1);
  }
}

function toggleDiet(dietName) {
  const omnivoreVal = 'Omnivore';

  if (dietName === omnivoreVal) {
    if (form.meal_plan_preferences.includes(omnivoreVal)) {
      form.meal_plan_preferences = [];
    } else {
      form.meal_plan_preferences = [omnivoreVal];
    }
    return;
  }

  if (form.meal_plan_preferences.includes(omnivoreVal)) {
    form.meal_plan_preferences = [];
  }

  const index = form.meal_plan_preferences.indexOf(dietName);
  if (index === -1) {
    form.meal_plan_preferences.push(dietName);
  } else {
    form.meal_plan_preferences.splice(index, 1);
  }
}

function toggleDislikedIngredient(ingredientName) {
  const index = form.disliked_ingredients.indexOf(ingredientName);
  if (index === -1) {
    form.disliked_ingredients.push(ingredientName);
  } else {
    form.disliked_ingredients.splice(index, 1);
  }
}

function addCustomIngredient(event) {
  event.preventDefault();
  const rawInput = currentCustomInput.value.trim();
  if (rawInput) {
    const newTags = rawInput
      .split(',')
      .map((tag) => tag.trim())
      .filter((tag) => tag);
    newTags.forEach((tag) => {
      if (
        !form.custom_dislikes.includes(tag) &&
        !form.disliked_ingredients.includes(tag)
      ) {
        form.custom_dislikes.push(tag);
      }
    });
    currentCustomInput.value = '';
  }
}

function removeCustomIngredient(index) {
  form.custom_dislikes.splice(index, 1);
}

function submitQuiz() {
  sessionStorage.removeItem('zeroWasteQuiz');

  form.post(route('quiz.save-session'));
}
</script>

<template>
  <div
    class="bg-background text-on-background font-body-md selection:bg-primary-container selection:text-on-primary-container flex min-h-screen flex-col antialiased"
  >
    <header
      v-if="currentStep >= 1 && currentStep <= 7"
      class="px-gutter max-w-container-max relative z-10 mx-auto mt-4 flex w-full shrink-0 flex-col items-center pt-8 md:mt-8"
    >
      <div class="w-full max-w-3xl">
        <div class="mb-2 flex items-center justify-between">
          <span
            class="font-label-md text-label-md text-on-surface-variant tracking-wider uppercase"
            >Step {{ currentStep }} of 7</span
          >
          <span class="font-label-md text-label-md text-primary font-bold"
            >{{ Math.round((currentStep / 7) * 100) }}%</span
          >
        </div>
        <div
          class="bg-surface-container-highest h-2 w-full overflow-hidden rounded-full shadow-inner"
        >
          <div
            class="bg-primary h-full rounded-full transition-all duration-500 ease-out"
            :style="{ width: `${(currentStep / 7) * 100}%` }"
          ></div>
        </div>
      </div>
    </header>

    <main
      class="max-w-container-max px-gutter relative mx-auto mb-20 flex w-full flex-1 flex-col items-center justify-center py-12"
    >
      <div v-if="currentStep === 1" class="flex w-full max-w-3xl flex-col">
        <div class="mb-12 text-left md:text-center">
          <h1 class="font-display text-display text-on-background mb-4">
            What are your primary goals?
          </h1>
          <p class="font-body-lg text-body-lg text-on-surface-variant">
            Select all that apply.
          </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <button
            v-for="goal in availableGoals"
            :key="goal.value"
            type="button"
            class="group relative flex cursor-pointer items-center justify-between rounded-xl border-2 p-6 text-left shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-200 ease-in-out"
            :class="[
              form.goals.includes(goal.value)
                ? 'bg-primary-container/10 border-primary scale-[1.02] shadow-[0px_10px_30px_rgba(0,0,0,0.08)]'
                : 'bg-surface-container-lowest border-surface-variant hover:border-primary hover:scale-[1.02] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]',
              goal.fullWidth ? 'md:col-span-2' : '',
            ]"
            @click="toggleGoal(goal.value)"
          >
            <div class="flex items-center gap-4">
              <span
                class="material-symbols-outlined text-[32px] transition-colors"
                :class="
                  form.goals.includes(goal.value)
                    ? 'text-primary'
                    : 'text-outline group-hover:text-primary'
                "
                >{{ goal.icon }}</span
              >
              <span
                class="font-body-md text-body-md font-semibold transition-colors"
                :class="
                  form.goals.includes(goal.value)
                    ? 'text-primary'
                    : 'text-on-surface group-hover:text-primary'
                "
                >{{ goal.title }}</span
              >
            </div>
            <span
              v-if="form.goals.includes(goal.value)"
              class="material-symbols-outlined text-primary font-bold"
              style="font-variation-settings: 'FILL' 1"
              >check_circle</span
            >
          </button>
        </div>

        <div class="mt-10 mb-6 flex w-full justify-center">
          <Link
            :href="route('login')"
            class="bg-surface-container-low hover:bg-surface-container-high text-primary font-label-md text-label-md border-surface-variant inline-flex items-center gap-2 rounded-full border px-6 py-2 shadow-sm transition-colors"
          >
            <span class="material-symbols-outlined text-[18px]">login</span>
            Already have an account? Log in
          </Link>
        </div>
      </div>

      <div v-else-if="currentStep === 2" class="flex w-full max-w-3xl flex-col">
        <header class="mb-10 flex flex-col gap-4 text-center">
          <h1 class="font-display text-display text-on-surface">
            How do you prefer to eat?
          </h1>
        </header>

        <div class="flex flex-wrap justify-center gap-4">
          <div
            v-for="diet in availableDiets"
            :key="diet.name"
            class="relative flex w-full cursor-pointer flex-col items-center rounded-xl border-2 p-4 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 sm:w-[calc(50%-8px)] md:w-[calc(33.333%-11px)]"
            :class="
              form.meal_plan_preferences.includes(diet.name)
                ? 'bg-tertiary/10 border-primary-container'
                : 'bg-surface-container-lowest border-surface-variant hover:border-primary hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]'
            "
            @click="toggleDiet(diet.name)"
          >
            <div
              v-if="form.meal_plan_preferences.includes(diet.name)"
              class="bg-primary-container text-on-primary absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full"
            >
              <span
                class="material-symbols-outlined text-sm"
                style="font-variation-settings: 'FILL' 1"
                >check</span
              >
            </div>

            <span
              class="material-symbols-outlined mb-2 text-4xl"
              :class="
                form.meal_plan_preferences.includes(diet.name)
                  ? 'text-primary'
                  : 'text-outline'
              "
              style="font-variation-settings: 'FILL' 0"
              >{{ diet.icon }}</span
            >
            <span
              class="font-headline-md text-body-md text-on-surface font-semibold"
              >{{ diet.name }}</span
            >
          </div>
        </div>
      </div>

      <div v-else-if="currentStep === 3" class="flex w-full max-w-3xl flex-col">
        <header class="mb-10 flex flex-col gap-4 text-center">
          <h1 class="font-display text-display text-on-surface">
            Any ingredients we should completely avoid?
          </h1>
        </header>

        <div class="group relative mb-8 w-full">
          <span
            class="material-symbols-outlined text-on-surface-variant absolute top-1/2 left-4 -translate-y-1/2 transform"
            >search</span
          >
          <input
            v-model="currentCustomInput"
            type="text"
            placeholder="Search ingredients (e.g., Mushrooms, Cilantro)..."
            class="bg-surface-container-low font-body-lg text-body-lg focus:border-primary focus:bg-surface w-full rounded-xl border-2 border-transparent py-4 pr-4 pl-12 shadow-sm transition-colors duration-200 focus:outline-none"
            @keydown.enter="addCustomIngredient"
          />
        </div>

        <div
          v-if="
            form.custom_dislikes.length > 0 ||
            form.disliked_ingredients.length > 0
          "
          class="mb-10 w-full"
        >
          <div class="flex flex-wrap gap-3">
            <button
              v-for="(tag, index) in form.custom_dislikes"
              :key="'custom-' + index"
              class="bg-error-container/20 border-error-container text-on-surface font-label-md text-label-md flex cursor-pointer items-center gap-2 rounded-full border px-4 py-2 transition-all hover:scale-[1.02]"
              @click="removeCustomIngredient(index)"
            >
              <span>{{ tag }}</span
              ><span class="material-symbols-outlined text-error text-[16px]"
                >close</span
              >
            </button>
            <button
              v-for="(tag, index) in form.disliked_ingredients"
              :key="'std-' + index"
              class="bg-error-container/20 border-error-container text-on-surface font-label-md text-label-md flex cursor-pointer items-center gap-2 rounded-full border px-4 py-2 transition-all hover:scale-[1.02]"
              @click="toggleDislikedIngredient(tag)"
            >
              <span>{{ tag }}</span
              ><span class="material-symbols-outlined text-error text-[16px]"
                >close</span
              >
            </button>
          </div>
        </div>

        <div v-if="!selectedCategory" class="w-full">
          <h3
            class="font-label-md text-label-md text-on-surface-variant mb-4 tracking-widest uppercase"
          >
            Browse Categories
          </h3>
          <div class="mb-10 grid grid-cols-2 gap-4 md:grid-cols-4">
            <button
              v-for="category in props.categories"
              :key="category.id"
              class="group bg-surface-container-lowest border-surface-variant hover:border-primary hover:bg-surface-container-low flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-2xl border-2 p-8 shadow-sm transition-all hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]"
              @click="selectedCategory = category"
            >
              <span
                class="material-symbols-outlined text-primary mb-4 text-5xl transition-transform group-hover:scale-110"
                >{{ category.icon }}</span
              >
              <span
                class="font-headline-md text-body-md text-on-surface text-center font-bold"
                >{{ category.name }}</span
              >
            </button>
          </div>
          <h3
            class="font-label-md text-label-md text-on-surface-variant mb-4 tracking-widest uppercase"
          >
            Common Exclusions
          </h3>
          <div class="flex flex-wrap gap-3">
            <button
              v-for="suggestion in [
                'Shellfish',
                'Spicy Foods',
                'Soy',
                'Tree Nuts',
              ]"
              :key="suggestion"
              class="bg-surface-container-lowest border-surface-variant font-body-md text-body-md text-on-surface hover:bg-surface-container-low flex cursor-pointer items-center gap-2 rounded-full border px-4 py-2 shadow-sm transition-all hover:scale-[1.02]"
              @click="toggleDislikedIngredient(suggestion)"
            >
              <span
                class="material-symbols-outlined text-primary text-[18px]"
                >{{
                  form.disliked_ingredients.includes(suggestion)
                    ? 'check'
                    : 'add'
                }}</span
              >
              <span>{{ suggestion }}</span>
            </button>
          </div>
        </div>

        <div
          v-else
          class="bg-surface-container-lowest border-surface-variant mb-8 flex w-full flex-col rounded-2xl border p-6 shadow-sm md:p-8"
        >
          <button
            class="text-primary mb-6 flex items-center self-start text-sm font-bold hover:underline"
            @click="selectedCategory = null"
          >
            <span class="material-symbols-outlined mr-1 text-sm"
              >arrow_back</span
            >
            Back to Categories
          </button>
          <h3
            class="font-headline-md text-on-surface mb-6 flex items-center gap-3 text-2xl font-bold"
          >
            <span class="material-symbols-outlined text-primary text-3xl">{{
              selectedCategory.icon
            }}</span>
            {{ selectedCategory.name }}
          </h3>
          <div
            class="grid max-h-100 grid-cols-1 gap-3 overflow-y-auto pr-2 sm:grid-cols-2"
          >
            <label
              v-for="ingredient in selectedCategory.ingredients"
              :key="ingredient"
              class="flex cursor-pointer items-center rounded-xl border-2 p-4 transition-all"
              :class="
                form.disliked_ingredients.includes(ingredient)
                  ? 'bg-primary-container/10 border-primary'
                  : 'bg-surface border-surface-variant hover:border-primary hover:bg-surface-container-low'
              "
            >
              <input
                v-model="form.disliked_ingredients"
                type="checkbox"
                :value="ingredient"
                class="accent-primary text-primary focus:ring-primary border-outline mr-4 h-5 w-5 cursor-pointer rounded"
              />
              <span
                class="font-body-md font-medium"
                :class="
                  form.disliked_ingredients.includes(ingredient)
                    ? 'text-primary font-bold'
                    : 'text-on-surface'
                "
                >{{ ingredient }}</span
              >
            </label>
          </div>
        </div>
      </div>

      <div
        v-else-if="currentStep === 4"
        class="flex w-full max-w-xl flex-col items-center"
      >
        <div class="mb-12 max-w-xl text-center">
          <h1 class="font-display text-display text-on-surface mb-4">
            Set your daily calorie goal.
          </h1>
        </div>

        <div
          class="flex w-full max-w-md flex-col items-center justify-center gap-4"
        >
          <div class="flex w-full items-center justify-center gap-4">
            <button
              class="bg-surface-container-lowest border-surface-variant hover:border-primary hover:text-primary text-on-surface-variant flex h-16 w-16 shrink-0 items-center justify-center rounded-full border-2 shadow-sm transition-all active:scale-95"
              @click="decreaseCalories"
            >
              <span class="material-symbols-outlined text-3xl">remove</span>
            </button>
            <div class="group relative w-full max-w-50">
              <input
                v-model="form.daily_calorie_target"
                aria-label="Daily Calorie Goal"
                type="number"
                min="1300"
                class="text-primary-container bg-surface-container-lowest border-surface-variant focus:border-primary-container focus:ring-primary-container/20 hover:border-outline-variant w-full rounded-2xl border-2 px-2 py-6 text-center text-5xl font-bold shadow-sm transition-all duration-200 hover:shadow-md focus:ring-4 focus:outline-none"
                @blur="handleCalorieBlur"
                @input="showCalorieWarning = false"
              />
            </div>
            <button
              class="bg-surface-container-lowest border-surface-variant hover:border-primary hover:text-primary text-on-surface-variant flex h-16 w-16 shrink-0 items-center justify-center rounded-full border-2 shadow-sm transition-all active:scale-95"
              @click="increaseCalories"
            >
              <span class="material-symbols-outlined text-3xl">add</span>
            </button>
          </div>

          <div class="flex h-12 flex-col items-center text-center">
            <span class="font-headline-md text-headline-md text-outline"
              >kcal / day</span
            >
            <span
              v-if="showCalorieWarning"
              class="font-body-sm text-error mt-1 animate-pulse font-semibold transition-opacity"
            >
              Calories cannot be set lower than 1300 or higher than 4000.
            </span>
          </div>
        </div>
      </div>

      <div
        v-else-if="currentStep === 5"
        class="flex w-full max-w-xl flex-col items-center"
      >
        <header class="mb-10 flex flex-col gap-4 text-center">
          <h1 class="font-display text-display text-on-surface">
            What is your shopping priority?
          </h1>
        </header>

        <div class="flex w-full flex-col gap-4">
          <label
            class="group bg-surface relative flex cursor-pointer items-center rounded-xl border-2 p-6 transition-all duration-200 hover:-translate-y-0.5"
            :class="
              form.budget_or_comfort === 'Budget-friendly (Save money)'
                ? 'bg-primary-container/10 border-primary shadow-sm'
                : 'border-surface-container-highest hover:border-outline-variant'
            "
          >
            <input
              v-model="form.budget_or_comfort"
              type="radio"
              value="Budget-friendly (Save money)"
              class="sr-only"
            />
            <div
              class="mr-4 flex h-12 w-12 shrink-0 items-center justify-center rounded-full"
              :class="
                form.budget_or_comfort === 'Budget-friendly (Save money)'
                  ? 'bg-primary text-on-primary'
                  : 'bg-surface-container-high text-on-surface-variant'
              "
            >
              <span class="material-symbols-outlined text-2xl">savings</span>
            </div>
            <div class="flex-1">
              <h3
                class="font-headline-md text-headline-md text-on-surface text-lg"
              >
                Budget-friendly
              </h3>
            </div>
          </label>
          <label
            class="group bg-surface relative flex cursor-pointer items-center rounded-xl border-2 p-6 transition-all duration-200 hover:-translate-y-0.5"
            :class="
              form.budget_or_comfort === 'Convenience & Comfort (Save time)'
                ? 'bg-primary-container/10 border-primary shadow-sm'
                : 'border-surface-container-highest hover:border-outline-variant'
            "
          >
            <input
              v-model="form.budget_or_comfort"
              type="radio"
              value="Convenience & Comfort (Save time)"
              class="sr-only"
            />
            <div
              class="mr-4 flex h-12 w-12 shrink-0 items-center justify-center rounded-full"
              :class="
                form.budget_or_comfort === 'Convenience & Comfort (Save time)'
                  ? 'bg-primary text-on-primary'
                  : 'bg-surface-container-high text-on-surface-variant'
              "
            >
              <span class="material-symbols-outlined text-2xl"
                >fast_forward</span
              >
            </div>
            <div class="flex-1">
              <h3
                class="font-headline-md text-headline-md text-on-surface text-lg"
              >
                Convenience & Comfort
              </h3>
            </div>
          </label>
        </div>
      </div>

      <div
        v-else-if="currentStep === 6"
        class="flex w-full max-w-xl flex-col items-center"
      >
        <div class="mb-10 text-center">
          <h1 class="font-headline-lg text-headline-lg text-on-surface mb-4">
            How many people are you cooking for?
          </h1>
        </div>

        <div class="w-full space-y-4">
          <label
            v-for="option in householdOptions"
            :key="option.value"
            class="group relative flex cursor-pointer items-center rounded-xl border-2 p-6 transition-all duration-200 hover:-translate-y-0.5"
            :class="
              form.household_size === option.value
                ? 'bg-primary-container/10 border-primary scale-[1.02] shadow-sm'
                : 'bg-surface-container-lowest border-surface-variant hover:border-outline-variant'
            "
          >
            <input
              v-model="form.household_size"
              type="radio"
              :value="option.value"
              class="sr-only"
            />
            <div
              class="mr-4 flex h-12 w-12 shrink-0 items-center justify-center rounded-full transition-colors"
              :class="
                form.household_size === option.value
                  ? 'bg-primary text-on-primary shadow-sm'
                  : 'bg-surface-container-low text-on-surface-variant group-hover:bg-surface-variant'
              "
            >
              <span class="material-symbols-outlined text-2xl">{{
                option.icon
              }}</span>
            </div>
            <div class="flex-1">
              <h3
                class="font-headline-md text-headline-md text-on-surface text-lg"
              >
                {{ option.title }}
              </h3>
            </div>
          </label>
        </div>
      </div>

      <div
        v-else-if="currentStep === 7"
        class="flex w-full max-w-xl flex-col items-center"
      >
        <div class="mb-12 text-center">
          <h1 class="font-display text-display text-on-background mb-4">
            How much time do you usually have for meal prep?
          </h1>
        </div>

        <div class="flex w-full flex-col gap-4">
          <label
            v-for="time in prepTimeOptions"
            :key="time.value"
            class="group relative flex cursor-pointer items-center gap-4 rounded-xl border-2 p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]"
            :class="
              form.prep_time_preference === time.value
                ? 'bg-primary-container/10 border-primary'
                : 'bg-surface border-surface-container-highest hover:border-outline-variant hover:bg-surface-container-low'
            "
          >
            <div
              class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full transition-colors"
              :class="
                form.prep_time_preference === time.value
                  ? 'bg-primary text-on-primary'
                  : 'bg-surface-container-high text-on-surface-variant group-hover:bg-surface-variant'
              "
            >
              <span
                class="material-symbols-outlined text-2xl"
                :style="
                  form.prep_time_preference === time.value
                    ? 'font-variation-settings: \'FILL\' 1;'
                    : ''
                "
                >{{ time.icon }}</span
              >
            </div>
            <div class="flex flex-1 flex-col">
              <span
                class="font-headline-md text-headline-md text-on-background mb-1 text-lg leading-tight"
                >{{ time.title }}</span
              >
            </div>
            <div class="shrink-0 pl-4">
              <input
                v-model="form.prep_time_preference"
                type="radio"
                :value="time.value"
                class="text-primary border-outline focus:ring-primary focus:ring-offset-surface bg-surface h-5 w-5 cursor-pointer"
              />
            </div>
          </label>
        </div>
      </div>

      <div
        v-else-if="currentStep === 8"
        class="bg-background fixed inset-0 z-50 flex min-h-screen w-full flex-col md:flex-row"
      >
        <section
          class="bg-surface-container-lowest px-gutter flex w-full shrink-0 flex-col justify-center overflow-y-auto py-10 shadow-[0px_4px_20px_rgba(0,0,0,0.04)] md:w-1/2 md:px-16 lg:w-125"
        >
          <div class="mx-auto w-full max-w-md space-y-8">
            <header class="space-y-4 text-left">
              <div
                class="font-headline-md text-headline-md text-primary flex items-center gap-2 font-bold"
              >
                <span
                  class="material-symbols-outlined"
                  style="font-variation-settings: 'FILL' 1"
                  >eco</span
                >
                Smart & ZeroWaste
              </div>
              <h1 class="font-display text-display text-on-surface">
                Your custom menu is ready.
              </h1>
              <p class="font-body-lg text-body-lg text-on-surface-variant">
                We've built your perfectly portioned
                <span class="text-primary font-bold"
                  >{{ form.daily_calorie_target }} kcal/day</span
                >
                waste-free plan. Create an account to save your preferences and
                unlock your weekly plan.
              </p>
            </header>

            <div class="space-y-6">
              <button
                :disabled="form.processing"
                class="bg-primary-container text-on-primary-container font-headline-md text-headline-md flex w-full items-center justify-center gap-2 rounded-xl py-4 text-[18px] transition-all duration-200 hover:scale-[1.02] hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)] disabled:cursor-not-allowed disabled:opacity-70"
                @click="submitQuiz"
              >
                <span
                  v-if="form.processing"
                  class="material-symbols-outlined animate-spin"
                  >refresh</span
                >
                <span v-else class="material-symbols-outlined"
                  >arrow_forward</span
                >
                {{ form.processing ? 'Saving...' : 'Continue to Registration' }}
              </button>
            </div>

            <div class="pt-4 text-center">
              <p class="font-body-md text-body-md text-on-surface-variant">
                Already have an account?
                <Link
                  href="/login"
                  class="text-primary font-semibold hover:underline"
                  >Log in</Link
                >
              </p>
            </div>
            <button
              class="text-on-surface-variant font-label-md mt-2 w-full hover:underline"
              @click="currentStep--"
            >
              Return to Quiz
            </button>
          </div>
        </section>

        <section
          class="bg-tertiary-container relative hidden grow overflow-hidden md:block"
        >
          <img
            alt="Fresh ingredients"
            class="absolute inset-0 h-full w-full object-cover"
            src="https://lh3.googleusercontent.com/aida/AP1WRLtwwvFMTc6LaK8czYngA7SthS1NkiOtDEGBiRZIwpte02w0EZtS698ph74AvqE2eIZMX_hsWls8V-KBuD7yqoxv54npmtpDv-KVtkHMIdnUc-Co4LTJaTzN64TJWbM2syNwEhuWe3KAPcIpOaUSSQZzK1MXxcMRh3GBM8ShPjrqjs19ueobRQOmQD0AV100F9SWiBBocYbYwj6X_1ANxetAcUi0BGKkZ9uhBj07dXiRqEMIKZwpZAyEZX0"
          />
        </section>
      </div>
    </main>

    <div
      v-if="currentStep >= 1 && currentStep <= 7"
      class="bg-surface border-surface-container-highest fixed bottom-0 left-0 z-50 w-full shrink-0 border-t shadow-sm"
    >
      <nav
        class="px-gutter max-w-container-max mx-auto flex h-20 items-center justify-between py-4"
      >
        <button
          :disabled="currentStep === 1"
          class="text-on-surface-variant font-label-md text-label-md flex items-center justify-center rounded-xl px-6 py-3 transition-colors duration-200"
          :class="
            currentStep === 1
              ? 'cursor-not-allowed opacity-50'
              : 'hover:bg-surface-container-low hover:scale-[1.02] active:scale-95'
          "
          @click="prevStep"
        >
          <span class="material-symbols-outlined mr-2">arrow_back</span> Back
        </button>
        <button
          class="font-label-md text-label-md flex items-center justify-center rounded-xl px-8 py-3 font-bold transition-all duration-200"
          :disabled="isNextDisabled"
          :class="
            isNextDisabled
              ? 'bg-surface-container-high text-on-surface-variant cursor-not-allowed opacity-50'
              : 'bg-primary-container text-on-primary-container hover:scale-[1.02] hover:shadow-md active:scale-95'
          "
          @click="nextStep"
        >
          {{ currentStep === 7 ? 'View Menu' : 'Next' }}
          <span v-if="currentStep < 7" class="material-symbols-outlined ml-2"
            >arrow_forward</span
          >
        </button>
      </nav>
    </div>
  </div>
</template>

<style scoped>
.material-symbols-outlined {
  font-variation-settings:
    'FILL' 0,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24;
}
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type='number'] {
  -moz-appearance: textfield;
}
</style>
