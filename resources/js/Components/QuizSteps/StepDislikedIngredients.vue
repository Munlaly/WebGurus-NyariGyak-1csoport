<script setup lang="ts">
import { ref, watch } from 'vue';

let debounceTimeout: ReturnType<typeof setTimeout>;

const model = defineModel<{ id: number; label: string }[]>({ required: true });

const searchTerm = ref('');
const items = ref<{ id: number; label: string }[]>([]);
const loading = ref(false);

function removeIngredient(idToRemove: number) {
  model.value = model.value.filter((item) => item.id !== idToRemove);
}

// Clear the search after a selection is made
watch(
  () => model.value.length,
  (newLength, oldLength) => {
    if (newLength > oldLength) {
      searchTerm.value = '';
      items.value = [];
    }
  },
);

watch(searchTerm, (query) => {
  clearTimeout(debounceTimeout);

  if (!query) {
    items.value = [];
    return;
  }

  // Wait 300ms after the user stops typing before hitting the backend
  debounceTimeout = setTimeout(async () => {
    loading.value = true;

    try {
      const response = await fetch(route('ingredients.search', { q: query }));
      if (!response.ok) throw new Error('Search failed');

      const data = await response.json();

      // Map backend 'name' to the 'label' that Nuxt UI v4 expects
      items.value = data.map((item: { id: number; name: string }) => ({
        id: item.id,
        label: item.name,
      }));
    } catch (error) {
      console.error(error);
      items.value = [];
    } finally {
      loading.value = false;
    }
  }, 300);
});
</script>

<template>
  <div
    class="flex w-full max-w-2xl flex-col items-center space-y-10 text-center"
  >
    <div class="space-y-4">
      <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900">
        Ingredients to Exclude
      </h2>
      <p class="text-lg leading-relaxed text-slate-700">
        Search for specific ingredients you strongly dislike or are allergic to.
        We will ensure these never appear in your weekly plan.
      </p>
    </div>

    <div class="flex w-full max-w-md flex-col gap-4 text-left">
      <div v-if="model.length > 0" class="flex flex-wrap gap-2">
        <span
          v-for="item in model"
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

      <UFormField name="disliked_ingredients">
        <USelectMenu
          v-model="model"
          v-model:search-term="searchTerm"
          :items="items"
          :loading="loading"
          multiple
          size="lg"
          class="w-full bg-lime-100 hover:bg-lime-400"
          :ui="{
            content: 'z-[100]',
          }"
        >
          <UButton
            color="neutral"
            variant="outline"
            icon="i-lucide-search"
            class="w-full shadow-sm ring-1 ring-stone-400 transition-colors ring-inset"
          >
            <span class="w-fit rounded-md px-2 py-1 hover:bg-gray-50">{{
              searchTerm || 'Search e.g., mushrooms, cilantro...'
            }}</span>
          </UButton>

          <template #empty>
            <div class="p-3 text-center text-sm text-slate-500">
              No ingredients found.
            </div>
          </template>
        </USelectMenu>
      </UFormField>
    </div>
  </div>
</template>
