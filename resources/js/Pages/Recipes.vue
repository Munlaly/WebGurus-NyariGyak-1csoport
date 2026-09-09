<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import RecipeEditorModal from '../Components/Modals/RecipeEditorModal.vue';
import type { Recipe, IngredientOption } from '../Types/recipesInterfaces';

defineProps<{
  myRecipes: Recipe[];
  favoriteRecipes: Recipe[];
  ingredients: IngredientOption[];
}>();

const recipeToEdit = ref<Recipe | null>(null);
const activeTab = ref<'favorites' | 'mine'>('favorites');
const isEditorOpen = ref(false);

function openCreator() {
  recipeToEdit.value = null;
  isEditorOpen.value = true;
}

function editRecipe(recipe: Recipe) {
  recipeToEdit.value = recipe;
  isEditorOpen.value = true;
}

function deleteRecipe(id: number) {
  if (confirm('Are you sure you want to delete this custom recipe?')) {
    router.delete(route('recipes.destroy', id), { preserveScroll: true });
  }
}

function getImageUrl(path: string | null) {
  if (!path) return 'https://placehold.co/600x400?text=No+Image';
  if (path.startsWith('http')) return path;
  return `/storage/${path}`;
}

function handleImageError(event: Event) {
  const target = event.target as HTMLImageElement | null;
  if (target) {
    target.src = 'https://placehold.co/600x400?text=No+Image';
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex h-full w-full flex-col gap-6">
      <!-- Header -->
      <div
        class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
      >
        <div>
          <h1 class="font-headline-lg text-headline-lg text-on-surface">
            Recipes
          </h1>
          <p class="font-body-md text-on-surface-variant">
            View your liked meals and craft your own custom recipes.
          </p>
        </div>
        <button
          class="bg-primary text-on-primary flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 font-bold shadow-sm transition-opacity hover:opacity-90"
          @click="openCreator"
        >
          <span class="material-symbols-outlined text-[20px]"
            >restaurant_menu</span
          >
          Create Recipe
        </button>
      </div>

      <!-- Tabs -->
      <div class="border-outline-variant/50 flex gap-6 border-b">
        <button
          :class="[
            'pb-3 font-semibold transition-colors',
            activeTab === 'favorites'
              ? 'text-primary border-primary border-b-2'
              : 'text-on-surface-variant hover:text-on-surface',
          ]"
          @click="activeTab = 'favorites'"
        >
          Liked Meals
        </button>
        <button
          :class="[
            'pb-3 font-semibold transition-colors',
            activeTab === 'mine'
              ? 'text-primary border-primary border-b-2'
              : 'text-on-surface-variant hover:text-on-surface',
          ]"
          @click="activeTab = 'mine'"
        >
          My Recipes
        </button>
      </div>

      <!-- Grid Content -->
      <div
        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
      >
        <template v-if="activeTab === 'favorites'">
          <div
            v-for="recipe in favoriteRecipes"
            :key="recipe.id"
            class="bg-surface-container-lowest border-outline-variant/50 flex flex-col overflow-hidden rounded-2xl border shadow-sm"
          >
            <img
              :src="getImageUrl(recipe.image)"
              :alt="recipe.name"
              class="h-48 w-full object-cover"
              @error="handleImageError"
            />
            <div class="flex grow flex-col p-4">
              <h3
                class="font-label-lg text-on-surface mb-1 line-clamp-1 font-bold capitalize"
              >
                {{ recipe.name }}
              </h3>
              <p class="text-on-surface-variant mb-4 text-sm font-semibold">
                {{ recipe.calories }} kcal • {{ recipe.prep_time_minutes }} min
              </p>
            </div>
          </div>
          <div
            v-if="favoriteRecipes.length === 0"
            class="text-on-surface-variant bg-surface-container-lowest border-outline-variant/50 col-span-full rounded-2xl border border-dashed p-12 text-center"
          >
            <span class="material-symbols-outlined mb-2 text-4xl opacity-50"
              >favorite_border</span
            >
            <p class="font-semibold">You haven't liked any meals yet.</p>
          </div>
        </template>

        <template v-if="activeTab === 'mine'">
          <div
            v-for="recipe in myRecipes"
            :key="recipe.id"
            class="bg-surface-container-lowest border-outline-variant/50 group flex flex-col overflow-hidden rounded-2xl border shadow-sm"
          >
            <img
              :src="getImageUrl(recipe.image)"
              :alt="recipe.name"
              class="h-48 w-full object-cover"
              @error="handleImageError"
            />
            <div class="flex grow flex-col p-4">
              <h3
                class="font-label-lg text-on-surface mb-1 line-clamp-1 font-bold capitalize"
              >
                {{ recipe.name }}
              </h3>
              <p class="text-on-surface-variant mb-4 text-sm font-semibold">
                {{ recipe.calories }} kcal • {{ recipe.prep_time_minutes }} min
              </p>

              <div
                class="mt-auto flex justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100"
              >
                <button
                  class="bg-surface-container-high hover:bg-surface-variant text-on-surface material-symbols-outlined rounded-full p-2 text-[18px]"
                  @click="editRecipe(recipe)"
                >
                  edit
                </button>
                <button
                  class="bg-error-container text-on-error-container hover:bg-error hover:text-on-error material-symbols-outlined rounded-full p-2 text-[18px]"
                  @click="deleteRecipe(recipe.id)"
                >
                  delete
                </button>
              </div>
            </div>
          </div>
          <div
            v-if="myRecipes.length === 0"
            class="text-on-surface-variant bg-surface-container-lowest border-outline-variant/50 col-span-full rounded-2xl border border-dashed p-12 text-center"
          >
            <span class="material-symbols-outlined mb-2 text-4xl opacity-50"
              >menu_book</span
            >
            <p class="font-semibold">
              You haven't created any custom recipes yet.
            </p>
          </div>
        </template>
      </div>
    </div>

    <RecipeEditorModal
      :show="isEditorOpen"
      :recipe="recipeToEdit"
      :ingredients-list="ingredients"
      @close="isEditorOpen = false"
    />
  </AuthenticatedLayout>
</template>
