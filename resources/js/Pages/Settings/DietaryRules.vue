<script setup lang="ts">
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';
import type { RulesProps } from '../../Types/settingInterfaces.js';

const props = defineProps<RulesProps>();

const activeTab = 'rules';

const form = useForm({
  activeDiets: props.activeDiets,
  dislikedIngredients: props.dislikedIngredients,
});

// Mapped dietary options for Nuxt UI CheckBoxGroup
const dietaryItems = computed(() => {
  return props.availableDietOptions.map((diet) => ({
    value: String(diet.id),
    label: diet.name,
    description: diet.description || undefined,
  }));
});

const activeDietsStringModel = computed({
  get: () => form.activeDiets.map(String),
  set: (val: string[]) => {
    form.activeDiets = val.map(Number);
  },
});

// Async Ingredient Search Logic
const searchTerm = ref('');
const items = ref<{ id: number; label: string }[]>([]);
const loading = ref(false);
const searchError = ref('');

let debounceTimeout: ReturnType<typeof setTimeout>;
let abortController: AbortController | null = null;

onBeforeUnmount(() => {
  if (abortController) abortController.abort();
  clearTimeout(debounceTimeout);
});

watch(searchTerm, (query) => {
  clearTimeout(debounceTimeout);
  searchError.value = '';

  if (!query) {
    items.value = [];
    return;
  }

  debounceTimeout = setTimeout(async () => {
    // Cancel the previous fetch if it's still running
    if (abortController) abortController.abort();
    abortController = new AbortController();

    loading.value = true;

    try {
      const response = await fetch(route('ingredients.search', { q: query }), {
        signal: abortController.signal,
      });

      if (!response.ok) throw new Error('Search failed');

      const data = await response.json();
      items.value = data.map((item: { id: number; name: string }) => ({
        id: item.id,
        label: item.name,
      }));
    } catch (error: unknown) {
      if (error instanceof Error) {
        if (error.name !== 'AbortError') {
          console.error('Ingredient search error:', error);
          searchError.value = 'Failed to fetch ingredients. Please try again.';
          items.value = [];
        } else {
          console.error('An unexpected error occurred:', error);
          searchError.value = 'An unexpected error occurred.';
        }
      }
    } finally {
      loading.value = false;
    }
  }, 300);
});

function removeIngredient(idToRemove: number) {
  form.dislikedIngredients = form.dislikedIngredients.filter(
    (item) => item.id !== idToRemove,
  );
}

// --- Submission ---
const onSubmit = () => {
  form
    .transform((data) => ({
      activeDiets: data.activeDiets,
      dislikedIngredients: data.dislikedIngredients.map((item) => item.id),
    }))
    .put(route('settings.rules'), { preserveScroll: true });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
      @submit.prevent="onSubmit"
    >
      <!-- Diet Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">
            Dietary Preferences
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Select all diets that apply to your lifestyle.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.activeDiets">
            <UCheckboxGroup
              v-model="activeDietsStringModel"
              :items="dietaryItems"
              size="lg"
            />
          </UFormField>
        </div>
      </div>

      <!-- Avoided Ingredients Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">
            Excluded Ingredients
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Search for specific ingredients you strongly dislike or are allergic
            to. They will never appear in your meal plans.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.dislikedIngredients">
            <USelectMenu
              v-model="form.dislikedIngredients"
              v-model:search-term="searchTerm"
              :items="items"
              :loading="loading"
              multiple
              placeholder="e.g., mushrooms, cilantro..."
              size="lg"
              class="w-full max-w-md"
              :ui="{ content: 'z-[100]' }"
            />

            <p v-if="searchError" class="mt-2 text-sm text-red-500">
              {{ searchError }}
            </p>
          </UFormField>

          <!-- Selected Items Badges -->
          <div
            v-if="form.dislikedIngredients.length > 0"
            class="mt-4 flex flex-wrap gap-2"
          >
            <span
              v-for="item in form.dislikedIngredients"
              :key="item.id"
              class="bg-primary/10 text-primary hover:bg-primary/20 inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
            >
              {{ item.label }}
              <button
                type="button"
                class="text-primary hover:text-primary-focus focus:outline-none"
                aria-label="Remove ingredient"
                @click="removeIngredient(item.id)"
              >
                <UIcon name="i-lucide-x" class="h-4 w-4" />
              </button>
            </span>
          </div>
        </div>
      </div>

      <div class="mt-auto flex justify-end py-6">
        <UButton
          type="submit"
          color="primary"
          :loading="form.processing"
          class="px-6 py-2 text-sm md:px-8 md:py-3 md:text-base lg:text-lg"
        >
          Save Rules
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
