<script setup lang="ts">
const model = defineModel<'lose_weight' | 'maintain' | 'gain_muscle'>({
  required: true,
});

const goalOptions = [
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

    <div class="w-full max-w-md space-y-4 text-left">
      <label
        v-for="goal in goalOptions"
        :key="goal.value"
        class="group relative flex cursor-pointer items-start gap-4 rounded-xl border-2 p-6 transition-all duration-200 hover:-translate-y-0.5"
        :class="[
          model === goal.value
            ? 'bg-primary/10 border-primary shadow-sm'
            : 'bg-surface-container-lowest border-surface-variant hover:border-primary/50 hover:shadow-sm',
        ]"
      >
        <!-- Visually hidden radio input for accessibility and binding -->
        <input
          v-model="model"
          type="radio"
          :value="goal.value"
          class="sr-only"
        />

        <!-- Custom Radio Indicator -->
        <div
          class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition-colors"
          :class="
            model === goal.value
              ? 'border-primary bg-primary'
              : 'border-outline-variant group-hover:border-primary/50'
          "
        >
          <div
            v-if="model === goal.value"
            class="h-2 w-2 rounded-full bg-white"
          ></div>
        </div>

        <div class="flex flex-col">
          <span
            class="font-headline-md text-on-surface text-lg font-bold transition-colors"
            :class="model === goal.value ? 'text-primary' : ''"
          >
            {{ goal.label }}
          </span>
          <span class="text-on-surface-variant mt-1 text-sm leading-snug">
            {{ goal.description }}
          </span>
        </div>
      </label>
    </div>
  </div>
</template>
