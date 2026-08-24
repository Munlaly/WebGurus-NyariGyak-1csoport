<script setup lang="ts">
// Allow string to handle the initial empty state from QuizMain.vue
const model = defineModel<number | undefined>({ required: true });
</script>

<template>
  <div
    class="flex w-full max-w-2xl flex-col items-center space-y-10 text-center"
  >
    <div class="space-y-4">
      <h2
        class="font-display text-on-surface text-3xl font-bold tracking-tight"
      >
        Household Size
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        How many people will you be cooking for? We use this metric to scale
        recipe portions and accurately calculate your weekly grocery list.
      </p>
    </div>

    <div class="w-full max-w-xs text-left">
      <UFormField name="household_size" label="Number of Servings">
        <UInput
          v-model="model"
          type="number"
          min="1"
          size="lg"
          placeholder="e.g., 2"
          class="w-full"
        />
      </UFormField>
    </div>
  </div>
</template>
