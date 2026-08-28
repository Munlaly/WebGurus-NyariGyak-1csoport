<script setup lang="ts">
import { computed, useTemplateRef } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { parseDate } from '@internationalized/date';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';
import type { BiometricsProps } from '../../Types/settingInterfaces';

const props = defineProps<BiometricsProps>();

const activeTab = 'biometrics';

const form = useForm({
  sex: props.profile.sex,
  birthdate: props.profile.birthdate,
  height_cm: props.profile.height_cm,
  weight_kg: props.profile.weight_kg,
  baseline_activity: props.profile.baseline_activity,
});

const sexItems = [
  { label: 'Male', value: 'male' },
  { label: 'Female', value: 'female' },
];

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

const dateModel = computed({
  get: () => {
    if (!form.birthdate) return undefined;
    try {
      const dateOnly = form.birthdate.split('T')[0];
      return parseDate(dateOnly);
    } catch {
      return undefined;
    }
  },
  set: (val) => {
    form.birthdate = val ? val.toString() : '';
  },
});

const submitBiometrics = () => {
  form.put(route('settings.biometrics'), { preserveScroll: true });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
      @submit.prevent="submitBiometrics"
    >
      <!-- Biological Data Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Biological Profile
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Used to calculate your Base Metabolic Rate (BMR) with the Mifflin-St
            Jeor equation.
          </p>
        </div>
        <div class="space-y-6 md:col-span-2">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <UFormField
              label="Biological Sex"
              name="sex"
              :error="form.errors.sex"
            >
              <USelect
                v-model="form.sex"
                :items="sexItems"
                class="w-full"
                :ui="{ content: 'z-[100]' }"
              />
            </UFormField>

            <UFormField
              label="Date of Birth"
              name="birthdate"
              :error="form.errors.birthdate"
            >
              <UInputDate ref="inputDate" v-model="dateModel" class="w-full">
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
        </div>
      </div>

      <!-- Body Metrics Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Body Metrics
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Update your weight dynamically to keep your caloric targets
            accurate.
          </p>
        </div>
        <div class="md:col-span-2">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <UFormField
              label="Height (cm)"
              name="height_cm"
              :error="form.errors.height_cm"
            >
              <UInput
                v-model="form.height_cm"
                type="number"
                min="50"
                max="300"
                placeholder="e.g., 180"
                class="w-full"
              />
            </UFormField>

            <UFormField
              label="Weight (kg)"
              name="weight_kg"
              :error="form.errors.weight_kg"
            >
              <UInput
                v-model="form.weight_kg"
                type="number"
                min="30"
                max="500"
                step="0.1"
                placeholder="e.g., 75.5"
                class="w-full"
              />
            </UFormField>
          </div>
        </div>
      </div>

      <!-- Baseline Activity Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="px-1 text-lg font-bold text-gray-900 dark:text-white">
            Baseline Activity
          </h2>
          <p class="mt-1 px-1 text-sm text-gray-500 dark:text-gray-400">
            Reflects your daily life and work routine, strictly excluding
            dedicated exercise.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField
            name="baseline_activity"
            :error="form.errors.baseline_activity"
          >
            <URadioGroup
              v-model="form.baseline_activity"
              :items="activityItems"
              variant="card"
            />
          </UFormField>
        </div>
      </div>

      <div class="mt-auto flex justify-end py-6">
        <UButton
          type="submit"
          color="primary"
          :loading="form.processing"
          class="px-6 py-2 text-sm md:px-8 md:py-3 md:text-base lg:text-lg"
        >
          Save Biometrics
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
