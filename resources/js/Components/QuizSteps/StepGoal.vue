<script setup lang="ts">
const model = defineModel<'lose_weight' | 'maintain' | 'gain_muscle'>({
  required: true,
});

const goalItems = [
  {
    label: 'Lose Weight',
    value: 'lose_weight',
    description: 'Caloric deficit to shed fat.',
  },
  {
    label: 'Maintain Current Form',
    value: 'maintain',
    description: 'Caloric balance to sustain your current weight.',
  },
  {
    label: 'Gain Muscle',
    value: 'gain_muscle',
    description: 'Caloric surplus to support muscle growth.',
  },
];
</script>

<template>
  <div
    class="flex w-full max-w-2xl flex-col items-center space-y-10 text-center"
  >
    <div class="space-y-4">
      <h2
        class="font-display text-on-surface text-3xl font-bold tracking-tight"
      >
        Define Your Objective
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        What is the primary fitness goal you want to achieve? Your weekly
        caloric target will be strictly calibrated based on this selection.
      </p>
    </div>

    <div class="w-full max-w-md text-left">
      <UFormField name="fitness_goal">
        <URadioGroup
          v-model="model"
          :items="goalItems"
          variant="card"
          size="lg"
          color="primary"
          :ui="{
            label: 'text-slate-900 dark:text-white font-semibold',
            description: 'text-slate-700 dark:text-gray-300 text-sm',
            item: 'mt-2 border-slate-300',
          }"
        />
      </UFormField>
    </div>
  </div>
</template>
