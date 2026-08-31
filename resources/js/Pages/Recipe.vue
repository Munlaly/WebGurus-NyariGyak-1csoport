<script setup lang="ts">
interface RecipeProps {
  id: number;
  title: string;
  prepTime: number;
  calories: number;
  imageUrl: string;
  imageAlt: string;
  macros: {
    protein: number;
    carbs: number;
    fat: number;
  };
  ingredients: Array<{ name: string; amount: number; unit: string | null }>;
  instructions: Array<string>;
}

defineProps<{
  recipe: RecipeProps;
}>();

const goBack = () => {
  window.history.back();
};

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement | null;

  if (target) {
    target.src = 'https://placehold.co/600x400?text=No+Image';
  }
};
</script>

<template>
  <main class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- 1. Sticky Header Wrapper  -->
    <div
      class="sticky top-0 z-50 border-b border-gray-200/50 bg-gray-50/90 px-8 py-6 backdrop-blur-md dark:border-gray-800/50 dark:bg-gray-900/90"
    >
      <div class="mx-auto max-w-7xl">
        <header class="grid grid-cols-3 items-center">
          <!-- Left Col-->
          <div class="flex justify-start">
            <UButton
              size="xl"
              variant="soft"
              color="primary"
              icon="i-heroicons-arrow-left"
              class="rounded-full p-3 shadow-sm transition-transform hover:-translate-x-1"
              @click="goBack"
            />
          </div>

          <!-- Center -->
          <div class="flex justify-center">
            <h1
              class="font-headline-lg text-center text-[32px] font-bold text-gray-900 dark:text-white"
            >
              {{ recipe.title }}
            </h1>
          </div>

          <!-- Right Col -->
          <div></div>
        </header>
      </div>
    </div>

    <!-- 2. Main Content Area -->
    <div class="p-8">
      <!-- Main Grid -->
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Left Column: Ingredients & Macros -->

        <div
          class="flex h-fit flex-col gap-8 lg:sticky lg:top-32 lg:col-span-1"
        >
          <!-- Ingredients UCard -->
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
                <span class="text-gray-500 dark:text-gray-400"
                  >{{ ingredient.amount }} {{ ingredient.unit || '' }}</span
                >
              </li>
            </ul>
          </UCard>

          <!-- Macros UCard -->
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
                  >{{ recipe.macros.protein }} g</UBadge
                >
              </li>
              <li class="flex items-center justify-between pt-4">
                <span class="font-medium">Carbs</span>
                <UBadge color="gray" variant="soft"
                  >{{ recipe.macros.carbs }} g</UBadge
                >
              </li>
              <li class="flex items-center justify-between pt-4">
                <span class="font-medium">Fat</span>
                <UBadge color="orange" variant="soft"
                  >{{ recipe.macros.fat }} g</UBadge
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
            class="relative mb-8 aspect-video w-full overflow-hidden rounded-xl shadow-sm"
          >
            <img
              class="h-full w-full object-cover"
              :src="recipe.imageUrl"
              :alt="recipe.imageAlt"
              @error="handleImageError"
            />

            <!-- Prep Time Overlay -->
            <div class="absolute top-4 left-4">
              <UBadge
                color="white"
                variant="solid"
                size="lg"
                class="gap-2 bg-white/90 px-4 py-2 text-lg font-bold text-gray-900 shadow-lg backdrop-blur-md"
              >
                <UIcon name="i-heroicons-clock" class="text-xl" />
                {{ recipe.prepTime }} MIN PREP
              </UBadge>
            </div>
          </div>

          <!-- Instructions UCard -->
          <UCard class="shadow-sm">
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

            <ol class="space-y-5">
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
                    class="text-lg leading-relaxed text-gray-700 dark:text-gray-300"
                  >
                    {{ step }}
                  </p>
                </div>
              </li>
            </ol>
          </UCard>
        </div>
      </div>
    </div>
  </main>
</template>
