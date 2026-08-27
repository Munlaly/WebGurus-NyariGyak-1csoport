<script setup lang="ts">
const model = defineModel<{
  monday: 'rest' | 'moderate' | 'heavy';
  tuesday: 'rest' | 'moderate' | 'heavy';
  wednesday: 'rest' | 'moderate' | 'heavy';
  thursday: 'rest' | 'moderate' | 'heavy';
  friday: 'rest' | 'moderate' | 'heavy';
  saturday: 'rest' | 'moderate' | 'heavy';
  sunday: 'rest' | 'moderate' | 'heavy';
}>({ required: true });

const days = [
  { key: 'monday', label: 'Monday' },
  { key: 'tuesday', label: 'Tuesday' },
  { key: 'wednesday', label: 'Wednesday' },
  { key: 'thursday', label: 'Thursday' },
  { key: 'friday', label: 'Friday' },
  { key: 'saturday', label: 'Saturday' },
  { key: 'sunday', label: 'Sunday' },
] as const;

const activityOptions = [
  { label: 'Rest (No training)', value: 'rest' },
  { label: 'Moderate (Cardio, light weights)', value: 'moderate' },
  { label: 'Heavy (Intense workout)', value: 'heavy' },
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
        Weekly Training Schedule
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        Adjust the days you actively train. We use this to calculate your daily
        caloric fluctuations and optimize recovery meals.
      </p>
    </div>

    <div class="w-full max-w-md space-y-6 text-left">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div
          v-for="day in days"
          :key="day.key"
          :class="{ 'md:col-span-2': day.key === 'sunday' }"
        >
          <UFormField :label="day.label" :name="`exercise_schedule.${day.key}`">
            <USelect
              v-model="model[day.key]"
              :items="activityOptions"
              size="lg"
              class="w-full"
              :ui="{ content: 'z-[100]' }"
            />
          </UFormField>
        </div>
      </div>
    </div>
  </div>
</template>
