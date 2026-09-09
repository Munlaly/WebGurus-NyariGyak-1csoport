<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionModal from './ActionModal.vue';
import type {
  IngredientOption,
  Recipe,
  RecipeIngredient,
} from '../../Types/recipesInterfaces';

const props = defineProps<{
  show: boolean;
  recipe?: Recipe | null;
  ingredientsList: IngredientOption[];
}>();

const emit = defineEmits(['close']);

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
  is_public: false,
  image: null as File | null,
  ingredients: [] as {
    id: number;
    name: string;
    amount: number;
    unit: string;
  }[],
});

watch(
  () => props.show,
  (isOpen) => {
    if (isOpen) {
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
        form.ingredients = props.recipe.ingredients.map(
          (i: RecipeIngredient) => ({
            id: i.id,
            name: i.name,
            amount: i.pivot?.amount || 1,
            unit: i.pivot?.unit || 'pcs',
          }),
        );
      } else {
        form.reset();
        form._method = 'post';
      }
    }
  },
);

function addIngredient() {
  form.ingredients.push({ id: 0, name: '', amount: 1, unit: 'pcs' });
}

function removeIngredient(index: number) {
  form.ingredients.splice(index, 1);
}

function handleImageUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0];
  if (file) form.image = file;
}

function submit() {
  const routeName = props.recipe
    ? route('recipes.update', props.recipe.id)
    : route('recipes.store');
  form.post(routeName, {
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
    :title="recipe ? 'Edit Recipe' : 'Create Custom Recipe'"
    :processing="form.processing"
    :submit-text="recipe ? 'Save Changes' : 'Create Recipe'"
    submit-variant="primary"
    @close="emit('close')"
    @submit="submit"
  >
    <div class="flex flex-col gap-5">
      <!-- Image Upload -->
      <div>
        <label class="font-label-sm text-on-surface-variant mb-1 block"
          >Recipe Image</label
        >
        <input
          type="file"
          accept="image/*"
          class="file:bg-primary/10 file:text-primary hover:file:bg-primary/20 text-sm transition-all file:mr-4 file:rounded-xl file:border-0 file:px-4 file:py-2 file:text-sm file:font-semibold"
          @change="handleImageUpload"
        />
        <p v-if="form.errors.image" class="text-error mt-1 text-xs">
          {{ form.errors.image }}
        </p>
      </div>

      <!-- Basic Info -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="font-label-sm text-on-surface-variant mb-1 block"
            >Recipe Name</label
          >
          <input
            v-model="form.name"
            type="text"
            class="border-outline-variant bg-surface-container-lowest focus:ring-primary w-full rounded-xl"
            required
          />
          <p v-if="form.errors.name" class="text-error mt-1 text-xs">
            {{ form.errors.name }}
          </p>
        </div>
        <div>
          <label class="font-label-sm text-on-surface-variant mb-1 block"
            >Prep Time (min)</label
          >
          <input
            v-model="form.prep_time_minutes"
            type="number"
            class="border-outline-variant bg-surface-container-lowest focus:ring-primary w-full rounded-xl"
            required
          />
        </div>
        <div>
          <label class="font-label-sm text-on-surface-variant mb-1 block"
            >Calories</label
          >
          <input
            v-model="form.calories"
            type="number"
            class="border-outline-variant bg-surface-container-lowest focus:ring-primary w-full rounded-xl"
            required
          />
        </div>
      </div>

      <!-- Macros -->
      <div class="grid grid-cols-3 gap-4">
        <div>
          <label class="font-label-sm text-on-surface-variant mb-1 block"
            >Protein (g)</label
          >
          <input
            v-model="form.protein"
            type="number"
            class="border-outline-variant bg-surface-container-lowest focus:ring-primary w-full rounded-xl"
            required
          />
        </div>
        <div>
          <label class="font-label-sm text-on-surface-variant mb-1 block"
            >Fat (g)</label
          >
          <input
            v-model="form.fat"
            type="number"
            class="border-outline-variant bg-surface-container-lowest focus:ring-primary w-full rounded-xl"
            required
          />
        </div>
        <div>
          <label class="font-label-sm text-on-surface-variant mb-1 block"
            >Carbs (g)</label
          >
          <input
            v-model="form.carbs"
            type="number"
            class="border-outline-variant bg-surface-container-lowest focus:ring-primary w-full rounded-xl"
            required
          />
        </div>
      </div>

      <!-- Meal Types -->
      <div>
        <label class="font-label-sm text-on-surface-variant mb-2 block"
          >Meal Types</label
        >
        <div class="flex flex-wrap gap-4">
          <label
            v-for="type in ['breakfast', 'lunch', 'dinner', 'snack']"
            :key="type"
            class="flex items-center gap-2"
          >
            <input
              v-model="form.meal_types"
              type="checkbox"
              :value="type"
              class="text-primary focus:ring-primary rounded"
            />
            <span class="text-sm font-medium capitalize">{{ type }}</span>
          </label>
        </div>
        <p v-if="form.errors.meal_types" class="text-error mt-1 text-xs">
          {{ form.errors.meal_types }}
        </p>
      </div>

      <!-- Ingredients Builder -->
      <div class="border-outline-variant mt-2 border-t pt-4">
        <div class="mb-3 flex items-center justify-between">
          <label class="font-label-sm text-on-surface-variant block font-bold"
            >Ingredients</label
          >
          <button
            type="button"
            class="text-primary bg-primary/10 rounded-full px-3 py-1 text-xs font-bold hover:underline"
            @click="addIngredient"
          >
            + Add Ingredient
          </button>
        </div>

        <div class="flex max-h-48 flex-col gap-2 overflow-y-auto pr-2">
          <div
            v-for="(ing, index) in form.ingredients"
            :key="index"
            class="flex items-center gap-2"
          >
            <select
              v-model="ing.id"
              class="border-outline-variant bg-surface-container-lowest flex-1 rounded-lg p-2 text-sm"
              required
            >
              <option disabled :value="0">Select ingredient...</option>
              <option
                v-for="option in ingredientsList"
                :key="option.id"
                :value="option.id"
              >
                {{ option.emoji }} {{ option.name }}
              </option>
            </select>
            <input
              v-model="ing.amount"
              type="number"
              step="0.1"
              class="border-outline-variant bg-surface-container-lowest w-20 rounded-lg p-2 text-sm"
              placeholder="Amt"
              required
            />
            <select
              v-model="ing.unit"
              class="border-outline-variant bg-surface-container-lowest w-24 rounded-lg p-2 text-sm"
            >
              <option value="pcs">pcs</option>
              <option value="g">g</option>
              <option value="ml">ml</option>
            </select>
            <button
              type="button"
              class="text-error hover:bg-error-container material-symbols-outlined rounded-full p-1 text-sm"
              @click="removeIngredient(index)"
            >
              close
            </button>
          </div>
          <p v-if="form.errors.ingredients" class="text-error mt-1 text-xs">
            Please add at least one valid ingredient.
          </p>
        </div>
      </div>

      <!-- Instructions -->
      <div>
        <label class="font-label-sm text-on-surface-variant mb-1 block"
          >Instructions</label
        >
        <textarea
          v-model="form.instructions"
          rows="4"
          class="border-outline-variant bg-surface-container-lowest focus:ring-primary w-full rounded-xl"
          required
        ></textarea>
      </div>
    </div>
  </ActionModal>
</template>
