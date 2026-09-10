<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionModal from './ActionModal.vue';
import type {
  Recipe,
  IngredientOption,
  RecipeIngredient,
} from '../../Types/recipesInterfaces';
import { useUnits } from '../../Composables/useUnits';

interface FormIngredient {
  id: number;
  name: string;
  amount: number;
  unit: string;
  raw_amount?: number;
  raw_unit?: string;
}

const props = defineProps<{
  show: boolean;
  recipe?: Recipe | null;
  ingredientsList: IngredientOption[];
  dietaryOptions: Array<{ id: number; name: string; description?: string }>;
}>();

const emit = defineEmits(['close']);
const { unitOptions, toStorageAmount, fromStorageAmount, getDisplayUnit } =
  useUnits();

const activeDropdownIndex = ref<number | null>(null);

// NEW: Store a local preview URL for the image
const imagePreview = ref<string | null>(null);

const form = useForm({
  _method: 'post',
  name: '',
  instructions: '',
  prep_time_minutes: 30,
  calories: 500,
  protein: 20,
  fat: 15,
  carbs: 50,
  meal_types: [] as string[],
  diets: [] as number[],
  is_public: false,
  image: null as File | null,
  ingredients: [] as {
    id: number;
    amount: number;
    unit: string;
    raw_amount?: number;
    raw_unit?: string;
  }[],
});

// Helper to format the saved DB path correctly
function getImageUrl(path: string | null) {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
}

watch(
  () => props.show,
  (isOpen) => {
    if (isOpen) {
      activeDropdownIndex.value = null;
      if (props.recipe) {
        form._method = 'put';
        form.name = props.recipe.name;
        form.instructions = props.recipe.instructions;
        form.prep_time_minutes = props.recipe.prep_time_minutes;
        form.calories = props.recipe.calories;
        form.protein = props.recipe.protein;
        form.fat = props.recipe.fat;
        form.carbs = props.recipe.carbs;
        form.meal_types = props.recipe.meal_types || [];
        form.is_public = props.recipe.is_public;

        // Load the existing image into the preview
        imagePreview.value = getImageUrl(props.recipe.image);

        form.ingredients = props.recipe.ingredients.map(
          (i: RecipeIngredient) => {
            const storedUnit = i.pivot?.unit || 'pcs';
            const storedAmount = i.pivot?.amount || 1;
            return {
              id: i.id,
              name: i.name,
              amount: fromStorageAmount(storedAmount, storedUnit),
              unit: storedUnit,
            };
          },
        );
      } else {
        form.reset();
        form._method = 'post';
        imagePreview.value = null; // Clear preview for new recipes
      }
    }
  },
);

function getFilteredIngredients(query: string) {
  if (!query) return props.ingredientsList;
  const lowerQuery = query.toLowerCase();
  return props.ingredientsList.filter((opt) =>
    opt.name.toLowerCase().includes(lowerQuery),
  );
}

function selectIngredient(ing: FormIngredient, option: IngredientOption) {
  ing.id = option.id;
  ing.name = option.name;
  activeDropdownIndex.value = null;
}

function addIngredient() {
  form.ingredients.push({ id: 0, name: '', amount: 1, unit: 'pcs' });
  setTimeout(() => {
    activeDropdownIndex.value = form.ingredients.length - 1;
  }, 50);
}

function removeIngredient(index: number) {
  form.ingredients.splice(index, 1);
  activeDropdownIndex.value = null;
}

function handleImageUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (file) {
    form.image = file;
    // NEW: Generate a temporary browser URL to preview the selected file immediately
    imagePreview.value = URL.createObjectURL(file);
  }
}

function submit() {
  const routeName = props.recipe
    ? route('recipes.update', props.recipe.id)
    : route('recipes.store');

  form
    .transform((data) => ({
      ...data,
      ingredients: data.ingredients.map((ing: FormIngredient) => ({
        ...ing,
        amount: toStorageAmount(ing.amount, ing.unit),
        raw_amount: ing.amount,
        raw_unit: getDisplayUnit(ing.unit),
      })),
    }))
    .post(routeName, {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
        emit('close');
      },
    });
}
</script>

<template>
  <ActionModal
    :show="show"
    max-width="3xl"
    :title="recipe ? 'Edit Recipe' : 'Create Custom Recipe'"
    :processing="form.processing"
    :submit-text="recipe ? 'Save Changes' : 'Create Recipe'"
    submit-variant="primary"
    @close="emit('close')"
    @submit="submit"
  >
    <div
      class="flex max-h-[70vh] scrollbar-thin flex-col gap-6 overflow-y-auto pr-2"
    >
      <!-- Image Upload with Preview -->
      <div>
        <label class="font-label-sm text-on-surface mb-2 block font-semibold"
          >Recipe Image</label
        >
        <div class="flex items-center gap-4">
          <!-- The visual preview box -->
          <div
            v-if="imagePreview"
            class="border-outline-variant bg-surface-container-low h-16 w-16 shrink-0 overflow-hidden rounded-xl border"
          >
            <img
              :src="imagePreview"
              class="h-full w-full object-cover"
              alt="Preview"
            />
          </div>

          <input
            type="file"
            accept="image/*"
            class="text-on-surface-variant file:bg-primary/10 file:text-primary hover:file:bg-primary/20 w-full text-sm transition-all file:mr-4 file:cursor-pointer file:rounded-xl file:border-0 file:px-4 file:py-2.5 file:text-sm file:font-bold"
            @change="handleImageUpload"
          />
        </div>
        <p v-if="form.errors.image" class="text-error mt-1 text-xs">
          {{ form.errors.image }}
        </p>
      </div>

      <!-- Basic Info -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label
            class="font-label-sm text-on-surface mb-1.5 block font-semibold"
            >Recipe Name</label
          >
          <input
            v-model="form.name"
            type="text"
            placeholder="e.g., Spicy Garlic Chicken"
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
            required
          />
          <p v-if="form.errors.name" class="text-error mt-1 text-xs">
            {{ form.errors.name }}
          </p>
        </div>
        <div>
          <label
            class="font-label-sm text-on-surface mb-1.5 block font-semibold"
            >Prep Time (min)</label
          >
          <input
            v-model="form.prep_time_minutes"
            type="number"
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
            required
          />
        </div>
        <div>
          <label
            class="font-label-sm text-on-surface mb-1.5 block font-semibold"
            >Calories</label
          >
          <input
            v-model="form.calories"
            type="number"
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
            required
          />
        </div>
      </div>

      <!-- Macros -->
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label
            class="font-label-sm text-on-surface mb-1.5 block font-semibold"
            >Protein (g)</label
          >
          <input
            v-model="form.protein"
            type="number"
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
            required
          />
        </div>
        <div>
          <label
            class="font-label-sm text-on-surface mb-1.5 block font-semibold"
            >Fat (g)</label
          >
          <input
            v-model="form.fat"
            type="number"
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
            required
          />
        </div>
        <div>
          <label
            class="font-label-sm text-on-surface mb-1.5 block font-semibold"
            >Carbs (g)</label
          >
          <input
            v-model="form.carbs"
            type="number"
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
            required
          />
        </div>
      </div>

      <!-- Meal Types -->
      <div>
        <label class="font-label-sm text-on-surface mb-2.5 block font-semibold"
          >Meal Types</label
        >
        <div class="flex flex-wrap gap-5">
          <label
            v-for="type in ['breakfast', 'lunch', 'dinner', 'snack']"
            :key="type"
            class="flex cursor-pointer items-center gap-2"
          >
            <input
              v-model="form.meal_types"
              type="checkbox"
              :value="type"
              class="border-outline-variant text-primary focus:ring-primary h-5 w-5 rounded transition-colors"
            />
            <span class="text-on-surface font-medium capitalize">{{
              type
            }}</span>
          </label>
        </div>
        <p v-if="form.errors.meal_types" class="text-error mt-1 text-xs">
          {{ form.errors.meal_types }}
        </p>
      </div>

      <!-- Ingredients Builder -->
      <div class="border-outline-variant mt-2 border-t pt-5">
        <div class="mb-4 flex items-center justify-between">
          <label class="font-label-sm text-on-surface block font-bold"
            >Ingredients</label
          >
          <button
            type="button"
            class="bg-primary/10 text-primary hover:bg-primary/20 flex items-center gap-1 rounded-full px-4 py-1.5 text-xs font-bold transition-colors"
            @click="addIngredient"
          >
            <span class="material-symbols-outlined text-[16px]">add</span>
            Add Ingredient
          </button>
        </div>

        <div class="flex flex-col gap-4">
          <div
            v-for="(ing, index) in form.ingredients"
            :key="index"
            class="bg-surface-container-low border-outline-variant/50 flex flex-col gap-3 rounded-xl border p-4 sm:flex-row sm:items-center"
          >
            <div class="relative flex-1 shrink">
              <input
                v-model="ing.name"
                type="text"
                placeholder="Type to search..."
                class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
                required
                @input="
                  ing.id = 0;
                  activeDropdownIndex = index;
                "
                @focus="activeDropdownIndex = index"
                @blur="activeDropdownIndex = null"
              />
              <span
                v-if="ing.name && !ing.id"
                class="material-symbols-outlined text-error absolute top-1/2 right-3 -translate-y-1/2 text-[20px]"
                title="Please select an item from the list"
                >error</span
              >
              <span
                v-else-if="ing.id"
                class="material-symbols-outlined text-primary absolute top-1/2 right-3 -translate-y-1/2 text-[20px]"
                >check_circle</span
              >

              <div
                v-if="activeDropdownIndex === index"
                class="border-outline-variant bg-surface-container-lowest absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-xl border shadow-xl"
              >
                <ul class="py-2">
                  <li
                    v-for="option in getFilteredIngredients(ing.name)"
                    :key="option.id"
                    class="hover:bg-surface-container-low text-on-surface cursor-pointer px-4 py-2 text-sm transition-colors"
                    @mousedown.prevent="selectIngredient(ing, option)"
                  >
                    {{ option.emoji }} {{ option.name }}
                  </li>
                  <li
                    v-if="getFilteredIngredients(ing.name).length === 0"
                    class="text-on-surface-variant px-4 py-3 text-sm italic"
                  >
                    No matching ingredients found.
                  </li>
                </ul>
              </div>
            </div>

            <div class="flex w-full shrink-0 items-center gap-2 sm:w-auto">
              <input
                v-model="ing.amount"
                type="number"
                step="0.1"
                min="0.1"
                class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-24 rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
                placeholder="Amt"
                required
              />

              <select
                v-model="ing.unit"
                class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-36 rounded-xl border px-3 py-3 text-sm transition-all outline-none focus:ring-2"
              >
                <option
                  v-for="opt in unitOptions"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>

              <button
                type="button"
                class="text-error hover:bg-error-container hover:text-error-600 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors"
                title="Remove ingredient"
                @click="removeIngredient(index)"
              >
                <span class="material-symbols-outlined text-[24px]"
                  >delete</span
                >
              </button>
            </div>
          </div>

          <div
            v-if="form.ingredients.length === 0"
            class="border-outline-variant/50 text-on-surface-variant rounded-xl border border-dashed py-8 text-center text-sm font-medium"
          >
            No ingredients added yet.
          </div>
          <p v-if="form.errors.ingredients" class="text-error mt-1 text-xs">
            Please add at least one valid ingredient.
          </p>
        </div>
      </div>

      <!-- Instructions -->
      <div>
        <label class="font-label-sm text-on-surface mb-1.5 block font-semibold"
          >Instructions</label
        >

        <div class="relative">
          <!-- Multi-line pseudo-placeholder that hides when form.instructions is filled -->
          <div
            v-if="!form.instructions"
            class="text-on-surface-variant/50 pointer-events-none absolute top-3.5 left-4 text-sm leading-relaxed"
          >
            1. Preheat the oven to 400°F...<br />
            2. Dice the onions<br />
            <span class="text-primary/70 font-medium"
              >(Press Enter after every step)</span
            >
          </div>

          <textarea
            v-model="form.instructions"
            rows="5"
            class="border-outline-variant bg-surface-container-lowest text-on-surface focus:border-primary focus:ring-primary w-full rounded-xl border px-4 py-3 text-sm transition-all outline-none focus:ring-2"
            required
          ></textarea>
        </div>
      </div>
    </div>
  </ActionModal>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
:deep(.dark) .scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #475569;
}
</style>
