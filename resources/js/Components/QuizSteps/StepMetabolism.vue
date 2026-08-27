<script setup lang="ts">
import { computed, useTemplateRef } from 'vue';
import { parseDate } from '@internationalized/date';

const sex = defineModel<'male' | 'female'>('sex', { required: true });
const birthdate = defineModel<string>('birthdate', { required: true });
const height = defineModel<number | string | undefined>('height', {
  required: true,
});
const weight = defineModel<number | string | undefined>('weight', {
  required: true,
});
const activity = defineModel<
  'sedentary' | 'lightly_active' | 'moderately_active' | 'very_active'
>('activity', { required: true });

const sexItems = [
  { label: 'Male', value: 'male' },
  { label: 'Female', value: 'female' },
];

const dateModel = computed({
  get: () => {
    if (!birthdate.value) return undefined;
    try {
      return parseDate(birthdate.value);
    } catch {
      return undefined;
    }
  },
  set: (val) => {
    birthdate.value = val ? val.toString() : '';
  },
});

const activityItems = [
  {
    label: 'Sedentary',
    value: 'sedentary',
    description:
      'Mostly sitting at a desk or resting. Very little movement throughout the day.',
  },
  {
    label: 'Lightly Active',
    value: 'lightly_active',
    description:
      'Standing or walking for part of the day (e.g., teacher, retail).',
  },
  {
    label: 'Moderately Active',
    value: 'moderately_active',
    description:
      'Walking frequently or lifting light loads (e.g., hospitality, delivery).',
  },
  {
    label: 'Very Active',
    value: 'very_active',
    description:
      'Heavy physical labor or highly demanding manual work (e.g., construction or farming).',
  },
];

const inputDate = useTemplateRef('inputDate');
</script>

<template>
  <div
    class="flex w-full max-w-2xl flex-col items-center space-y-10 text-center"
  >
    <div class="space-y-4">
      <h2
        class="font-display text-on-surface text-3xl font-bold tracking-tight"
      >
        Metabolic Profile
      </h2>
      <p class="text-on-surface-variant text-lg leading-relaxed">
        We use these metrics to accurately calculate your Base Metabolic Rate
        (BMR). Please note that your baseline activity should only reflect your
        daily life and work routine, strictly excluding dedicated exercise or
        training.
      </p>
    </div>

    <div class="w-full space-y-8 text-left">
      <!-- Group 1: Biological Data -->
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <UFormField label="Biological Sex" name="sex">
          <USelect
            v-model="sex"
            :items="sexItems"
            placeholder="Select your biological sex"
            class="mt-2"
            :ui="{ content: 'z-[100]' }"
          />
        </UFormField>

        <UFormField label="Date of Birth" name="birthdate">
          <UInputDate
            ref="inputDate"
            v-model="dateModel"
            size="lg"
            class="w-full"
          >
            <template #trailing>
              <UPopover
                :reference="inputDate?.inputsRef[3]?.$el"
                :ui="{ content: 'z-[100]' }"
              >
                <UButton
                  color="neutral"
                  variant="link"
                  size="sm"
                  icon="i-lucide-calendar"
                  aria-label="Select a date"
                  class="px-0"
                />
                <template #content>
                  <UCalendar v-model="dateModel" class="p-2" />
                </template>
              </UPopover>
            </template>
          </UInputDate>
        </UFormField>
      </div>

      <!-- Group 2: Body Metrics -->
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <UFormField label="Height (cm)" name="height_cm">
          <UInput
            v-model="height"
            type="number"
            size="lg"
            placeholder="e.g., 180"
            class="w-full"
          />
        </UFormField>

        <UFormField label="Weight (kg)" name="weight_kg">
          <UInput
            v-model="weight"
            type="number"
            size="lg"
            placeholder="e.g., 75"
            class="w-full"
          />
        </UFormField>
      </div>

      <!-- Group 3: Baseline Activity -->
      <UFormField label="Baseline Activity Level" name="baseline_activity">
        <URadioGroup
          v-model="activity"
          :items="activityItems"
          variant="card"
          class="mt-2"
        />
      </UFormField>
    </div>
  </div>
</template>
