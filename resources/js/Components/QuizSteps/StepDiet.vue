<script setup lang="ts">
const model = defineModel<number[]>({ required: true });

defineProps<{
  options: {
    id: number;
    name: string;
    description: string | null;
  }[];
}>();
</script>

<template>
  <div
    class="flex w-full max-w-2xl flex-col items-center space-y-10 text-center"
  >
    <div class="space-y-4">
      <h2
        class="font-display text-on-surface text-3xl font-bold tracking-tight"
      >
        Dietary Preferences
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        Do you follow any specific diets? Select all that apply so we can filter
        your meal plan accordingly.
      </p>
    </div>

    <div class="w-full max-w-md text-left">
      <UFormField name="meal_plan_preferences">
        <div class="mt-2 space-y-4">
          <UCheckbox
            v-for="diet in options"
            :key="diet.id"
            v-model="model"
            :value="diet.id"
            :label="diet.name"
            :description="diet.description || undefined"
            size="lg"
            class="hover:bg-surface-container-lowest rounded-xl border-2 border-transparent p-2 transition-all"
          />
        </div>
      </UFormField>
    </div>
  </div>
</template>
