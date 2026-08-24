<script setup lang="ts">
import { ref, watch } from 'vue';

const model = defineModel<{ id: number; label: string }[]>({ required: true });

const searchTerm = ref('');
const items = ref<{ id: number; label: string }[]>([]);
const loading = ref(false);

let debounceTimeout: ReturnType<typeof setTimeout>;

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
      <h2
        class="font-display text-on-surface text-3xl font-bold tracking-tight"
      >
        Ingredients to Exclude
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        Search for specific ingredients you strongly dislike or are allergic to.
        We will ensure these never appear in your weekly plan.
      </p>
    </div>

    <div class="w-full max-w-md text-left">
      <UFormField name="disliked_ingredients">
        <USelectMenu
          v-model="model"
          v-model:search-term="searchTerm"
          :items="items"
          :loading="loading"
          multiple
          placeholder="e.g., mushrooms, cilantro..."
          size="lg"
          class="w-full"
        />
      </UFormField>
    </div>
  </div>
</template>
