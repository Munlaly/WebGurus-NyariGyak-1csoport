<script setup lang="ts">
import { router } from '@inertiajs/vue3';

interface RecipeProps {
  id: number;
  title: string;
  prepTime: number;
  calories: number;
  imageUrl: string;
  imageAlt: string;
  isZeroWaste: boolean;
  macros: {
    protein: number;
    carbs: number;
    fat: number;
  };
  ingredients: Array<{ name: string; amount: string }>;
  instructions: Array<string>;
}

defineProps<{
  recipe: RecipeProps;
}>();

const goBack = () => {
  router.get('/dashboard');
};
</script>

<template>
  <main class="mx-auto max-w-5xl p-8">
    <!-- Header Area -->
    <header
      class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center"
    >
      <UButton
        variant="ghost"
        color="primary"
        icon="i-heroicons-arrow-left"
        class="w-full md:w-auto"
        @click="goBack"
      >
        Back to Planner
      </UButton>

      <h1
        class="font-headline-lg flex-1 text-center text-[32px] font-bold text-gray-900 dark:text-white"
      >
        {{ recipe.title }}
      </h1>

      <div class="flex w-full items-center justify-end md:w-auto">
        <UBadge
          color="gray"
          variant="solid"
          size="lg"
          class="gap-2 rounded-full px-4 py-2"
        >
          <UIcon name="i-heroicons-clock" class="text-lg" />
          {{ recipe.prepTime }} MIN PREP
        </UBadge>
      </div>
    </header>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
      <!-- Left Column: Ingredients & Macros -->
      <div class="flex flex-col gap-8 lg:col-span-1">
        <!-- Ingredients -->
        <UCard>
          <template #header>
            <h2
              class="flex items-center gap-2 text-[24px] font-bold text-gray-900 dark:text-white"
            >
              <UIcon name="i-heroicons-shopping-cart" class="text-primary" />
              Ingredients
            </h2>
          </template>

          <ul class="space-y-4 divide-y divide-gray-200 dark:divide-gray-800">
            <li
              v-for="(ingredient, index) in recipe.ingredients"
              :key="index"
              class="flex items-center justify-between pt-4 first:pt-0"
            >
              <span class="font-medium text-gray-900 dark:text-white">{{
                ingredient.name
              }}</span>
              <span class="text-gray-500 dark:text-gray-400">{{
                ingredient.amount
              }}</span>
            </li>
          </ul>
        </UCard>

        <!-- Macros -->
        <UCard>
          <template #header>
            <h2
              class="flex items-center gap-2 text-[24px] font-bold text-gray-900 dark:text-white"
            >
              <UIcon name="i-heroicons-chart-pie" class="text-primary" />
              Macros
            </h2>
          </template>

          <ul class="space-y-4 divide-y divide-gray-200 dark:divide-gray-800">
            <li class="flex items-center justify-between pt-4 first:pt-0">
              <span class="font-medium">Protein</span>
              <UBadge color="green" variant="soft"
                >{{ recipe.macros.protein }}g</UBadge
              >
            </li>
            <li class="flex items-center justify-between pt-4">
              <span class="font-medium">Carbs</span>
              <UBadge color="gray" variant="soft"
                >{{ recipe.macros.carbs }}g</UBadge
              >
            </li>
            <li class="flex items-center justify-between pt-4">
              <span class="font-medium">Fat</span>
              <UBadge color="orange" variant="soft"
                >{{ recipe.macros.fat }}g</UBadge
              >
            </li>
            <li
              class="flex items-center justify-between pt-4 text-[24px] font-bold"
            >
              <span>Calories</span>
              <span class="text-primary">{{ recipe.calories }}</span>
            </li>
          </ul>
        </UCard>
      </div>

      <!-- Right Column: Hero Image & Instructions -->
      <div class="lg:col-span-2">
        <!-- Hero Image Container -->
        <div
          class="relative mb-8 h-96 w-full overflow-hidden rounded-xl shadow-sm"
        >
          <img
            class="h-full w-full object-cover"
            :src="recipe.imageUrl"
            :alt="recipe.imageAlt"
          />
          <div v-if="recipe.isZeroWaste" class="absolute right-4 bottom-4">
            <UBadge
              color="primary"
              variant="solid"
              size="lg"
              class="bg-opacity-90 gap-2 px-4 py-2 shadow-lg backdrop-blur-sm"
            >
              <UIcon name="i-heroicons-sparkles" class="text-lg" />
              ZERO WASTE RECORD
            </UBadge>
          </div>
        </div>

        <!-- Instructions -->
        <UCard>
          <template #header>
            <h2
              class="flex items-center gap-2 text-[24px] font-bold text-gray-900 dark:text-white"
            >
              <UIcon
                name="i-heroicons-clipboard-document-list"
                class="text-primary"
              />
              Instructions
            </h2>
          </template>

          <ol class="space-y-8">
            <li
              v-for="(step, index) in recipe.instructions"
              :key="index"
              class="flex gap-6"
            >
              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-bold shadow-sm"
                :class="
                  index === 0
                    ? 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-400'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                "
              >
                {{ index + 1 }}
              </div>
              <div class="pt-1">
                <p
                  class="text-lg leading-relaxed text-gray-900 dark:text-gray-100"
                >
                  {{ step }}
                </p>
              </div>
            </li>
          </ol>
        </UCard>
      </div>
    </div>
  </main>
</template>
