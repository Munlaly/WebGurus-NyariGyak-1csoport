<script setup lang="ts">
const prepTimeOptions = [
  {
    label: 'Quick (Under 15 minutes)',
    value: 15,
    description: 'Fast, minimal-effort meals for busy schedules.',
  },
  {
    label: 'Standard (Under 30 minutes)',
    value: 30,
    description: 'Balanced recipes that fit easily into a daily routine.',
  },
  {
    label: 'Moderate (Under 45 minutes)',
    value: 45,
    description: 'Slightly more elaborate meals with multiple components.',
  },
  {
    label: 'No Limit (60+ minutes)',
    value: 60,
    description:
      'I enjoy cooking and do not mind spending extra time in the kitchen.',
  },
];

// Allowing string to handle the initial empty state from QuizMain.vue
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
        Preparation Time
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        How much time are you willing to spend preparing a single meal? We will
        filter your weekly recipes to match your schedule.
      </p>
    </div>

    <div class="w-full max-w-md text-left">
      <UFormField name="prep_time_preference">
        <URadioGroup
          v-model="model"
          :items="prepTimeOptions"
          variant="card"
          size="lg"
          :ui="{
            item: ' mt-2 ring-1 ring-stone-400',
          }"
        />
      </UFormField>
    </div>
  </div>
</template>
