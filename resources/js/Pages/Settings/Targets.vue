<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';
import { ref } from 'vue';

const props = defineProps<{
  userSettings: {
    mainGoal: string;
    calorieTarget: number;
  };
}>();

interface GoalItem {
  label: string;
  value: string;
  description: string;
}

const activeTab = 'targets';
const mainGoalItems = ref<GoalItem[]>([
  {
    label: 'Weight Loss',
    value: 'weightloss',
    description: 'Helps loose weight',
  },
  {
    label: 'Weight gain',
    value: 'weightgain',
    description: 'Helps gain weight (bulk)',
  },
  {
    label: 'Muscle gain',
    value: 'muscle',
    description: 'Helps build muscles',
  },
  {
    label: 'Healthy',
    value: 'general',
    description: 'General balanced diet',
  },
]);
const form = useForm({
  mainGoal: props.userSettings.mainGoal,
  caloriesTarget: props.userSettings.calorieTarget,
});

const isEditing = ref({
  mainGoal: false,
  calorieTarget: false,
});

const toggleEdit = (field: keyof typeof isEditing.value) => {
  isEditing.value[field] = !isEditing.value[field];
};

const submitGoal = () => {
  form.post('/settings/targets', {
    preserveScroll: true,
    onSuccess: () => {
      isEditing.value.mainGoal = false;
      isEditing.value.calorieTarget = false;
    },
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

          <UButton
            icon="i-heroicons-pencil-square"
            color="gray"
            variant="ghost"
            @click="toggleEdit('mainGoal')"
          />
        </div>
        <UFormField :error="form.errors.mainGoal">
          <URadioGroup
            v-model="form.mainGoal"
            :items="mainGoalItems"
            variant="table"
            :disabled="!isEditing.mainGoal"
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

          <UButton
            icon="i-heroicons-pencil-square"
            color="gray"
            variant="ghost"
            @click="toggleEdit('calorieTarget')"
          />
        </div>
        <UFormField :error="form.errors.caloriesTarget">
          <UInputNumber
            v-model="form.caloriesTarget"
            :disabled="!isEditing.calorieTarget"
          />
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
