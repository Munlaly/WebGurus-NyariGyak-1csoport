<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  options: {
    id: number;
    name: string;
    description: string | null;
  }[];
}>();

const model = defineModel<number[]>({ required: true });

// map the data for UCheckboxGroup
const dietaryItems = computed(() => {
  return props.options.map((diet) => ({
    value: String(diet.id),
    label: diet.name,
    description: diet.description || undefined,
  }));
});

// Converts UI's string array to number array
const stringModel = computed({
  get: () => model.value.map(String),
  set: (val: string[]) => {
    model.value = val.map(Number);
  },
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
          <UCheckboxGroup
            v-model="stringModel"
            :items="dietaryItems"
            size="lg"
            class="mt-2"
            variant="card"
          />
        </div>
      </UFormField>
    </div>
  </div>
</template>
