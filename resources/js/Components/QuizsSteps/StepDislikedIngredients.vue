<script setup lang="ts">
const model = defineModel<{ id: number; label: string }[]>({ required: true });

// Async search function triggered by user typing
async function searchIngredients(query: string) {
  if (!query) return [];

  try {
    const response = await fetch(route('ingredients.search', { q: query }));
    if (!response.ok) throw new Error('Search failed');

    const data = await response.json();

    return data.map((item: { id: number; name: string }) => ({
      id: item.id,
      label: item.name,
    }));
  } catch (error) {
    console.error(error);
    return [];
  }
}
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
          :search="searchIngredients"
          multiple
          placeholder="e.g., mushrooms, cilantro..."
          searchable-placeholder="Search ingredients..."
          by="id"
          size="lg"
          class="w-full"
        />
      </UFormField>
    </div>
  </div>
</template>
