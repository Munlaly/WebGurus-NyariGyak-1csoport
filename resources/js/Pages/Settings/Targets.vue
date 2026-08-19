<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';

const props = defineProps<{
  userSettings: {
    mainGoal: string;
    calorieTarget: number;
  };
}>();

const activeTab = 'targets';
const mainGoalItems = [
  {
    label: 'Weight Loss',
    value: 'weightloss',
    description: 'Helps lose weight',
  },
  {
    label: 'Weight gain',
    value: 'weightgain',
    description: 'Helps gain weight (bulk)',
  },
  { label: 'Muscle gain', value: 'muscle', description: 'Helps build muscles' },
  { label: 'Healthy', value: 'general', description: 'General balanced diet' },
];

const form = useForm({
  mainGoal: props.userSettings.mainGoal,
  calorieTarget: props.userSettings.calorieTarget,
});

const submitGoal = () => {
  form.post('/settings/targets', {
    preserveScroll: true,
  });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="mx-auto max-w-2xl space-y-6"
      @submit.prevent="submitGoal"
    >
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <h2
            class="font-display text-primary text-[24px] font-bold tracking-tight"
          >
            Main goal
          </h2>
        </div>
        <UFormField :error="form.errors.mainGoal">
          <URadioGroup
            v-model="form.mainGoal"
            :items="mainGoalItems"
            variant="table"
          >
          </URadioGroup>
        </UFormField>
      </UCard>
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <h2
            class="font-display text-primary text-[24px] font-bold tracking-tight"
          >
            Daily calorie Target
          </h2>
        </div>
        <UFormField :error="form.errors.calorieTarget">
          <UInputNumber v-model="form.calorieTarget" />
        </UFormField>
      </UCard>

      <div class="flex justify-end pt-4">
        <UButton type="submit" color="primary" :loading="form.processing">
          Apply settings
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
