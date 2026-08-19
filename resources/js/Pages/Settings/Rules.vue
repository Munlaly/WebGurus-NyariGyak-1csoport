<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import SettingsLayout from '../../Layouts/SettingsLayout.vue';

const dietOptions = [
  {
    label: 'Omnivore',
    value: 'omnivore',
    description: 'I eat everything',
    icon: 'i-heroicons-globe-americas',
  },
  {
    label: 'Vegetarian',
    value: 'vegetarian',
    description: 'No meat or poultry',
    icon: 'i-heroicons-leaf',
  },
  {
    label: 'Vegan',
    value: 'vegan',
    description: 'No animal products',
    icon: 'i-heroicons-sparkles',
  },
  {
    label: 'Gluten-Free',
    value: 'gluten_free',
    description: 'Avoid wheat & gluten',
    icon: 'i-heroicons-receipt-percent',
  },
  {
    label: 'Dairy-Free',
    value: 'dairy_free',
    description: 'No milk or cheese',
    icon: 'i-heroicons-beaker',
  },
  {
    label: 'Keto / Low-Carb',
    value: 'keto',
    description: 'High fat, low carb',
    icon: 'i-heroicons-scale',
  },
  {
    label: 'Nut-Free',
    value: 'nut_free',
    description: 'Allergy safe',
    icon: 'i-heroicons-shield-check',
  },
];

const householdOptions = [
  {
    label: 'Just for myself',
    value: '1_person',
    description: '1 person',
    icon: 'i-heroicons-user',
  },
  {
    label: 'Me and my partner',
    value: '2_people',
    description: '2 people',
    icon: 'i-heroicons-users',
  },
  {
    label: 'For the entire family',
    value: 'family',
    description: '3-5+ people',
    icon: 'i-heroicons-user-group',
  },
];

const prepTimeOptions = [
  { label: 'Lightning fast', value: 'fast', description: 'Under 20 minutes' },
  { label: 'Normal pace', value: 'normal', description: '30-45 minutes' },
  { label: 'Leisurely / Weekend', value: 'slow', description: 'Over 1 hour' },
];

const props = defineProps<{
  userSettings: {
    prepTime: string;
    numberOfPeople: string;
    dietType: string;
    avoidedIngredients: string;
  };
}>();

const activeTab = 'rules';

const form = useForm({
  prepTime: props.userSettings?.prepTime || 'normal',
  numberOfPeople: props.userSettings?.numberOfPeople || '1_person',
  dietType: props.userSettings?.dietType || 'omnivore',
  avoidedIngredients: props.userSettings?.avoidedIngredients || '',
});

const submitRule = () => {
  form.post('/settings/rules', { preserveScroll: true });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="flex flex-1 flex-col divide-y divide-gray-200 px-4 md:px-0 dark:divide-gray-800"
      @submit.prevent="submitRule"
    >
      <!-- Diet Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">
            Dietary Preference
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Select your baseline diet type.
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.dietType">
            <URadioGroup
              v-model="form.dietType"
              :items="dietOptions"
              variant="table"
            />
          </UFormField>
        </div>
      </div>

      <!-- Household Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">
            Household Size
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            How many people are you cooking for?
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.numberOfPeople">
            <URadioGroup
              v-model="form.numberOfPeople"
              :items="householdOptions"
              variant="table"
            />
          </UFormField>
        </div>
      </div>

      <!-- Prep Time Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">
            Prep Time
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            How much time do you usually have to cook?
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField :error="form.errors.prepTime">
            <URadioGroup
              v-model="form.prepTime"
              :items="prepTimeOptions"
              variant="table"
            />
          </UFormField>
        </div>
      </div>

      <!-- Avoided Ingredients Section -->
      <div class="grid grid-cols-1 gap-8 py-8 md:grid-cols-3">
        <div class="md:col-span-1">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">
            Avoided Ingredients
          </h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            List specific ingredients you want to completely avoid (allergies,
            dislikes).
          </p>
        </div>
        <div class="md:col-span-2">
          <UFormField
            :error="form.errors.avoidedIngredients"
            hint="Separate with commas"
          >
            <UInput
              v-model="form.avoidedIngredients"
              placeholder="e.g., Peanuts, Shellfish, Cilantro"
              class="w-full max-w-md"
            />
          </UFormField>
        </div>
      </div>

      <!-- Pinned to bottom with mt-auto -->
      <div class="mt-auto flex justify-end py-6">
        <UButton
          type="submit"
          color="primary"
          :loading="form.processing"
          class="px-6 py-2 text-sm md:px-8 md:py-3 md:text-base lg:text-lg"
        >
          Save Rules
        </UButton>
      </div>
    </UForm>
  </SettingsLayout>
</template>
