<script setup lang="ts">
import { ref } from 'vue';
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
  {
    label: 'Lightning fast',
    value: 'fast',
    description: 'Under 20 minutes',
  },
  {
    label: 'Normal pace',
    value: 'normal',
    description: '30-45 minutes',
  },
  {
    label: 'Leisurely / Weekend',
    value: 'slow',
    description: 'Over 1 hour',
  },
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

const isEditing = ref({
  prepTime: false,
  numberOfPeople: false,
  dietType: false,
  avoidedIngredients: false,
});

const toggleEdit = (field: keyof typeof isEditing.value) => {
  isEditing.value[field] = !isEditing.value[field];
};

const submitRule = () => {
  form.post('/settings/rules', {
    preserveScroll: true,
    onSuccess: () => {
      (
        Object.keys(isEditing.value) as Array<keyof typeof isEditing.value>
      ).forEach((key) => {
        isEditing.value[key] = false;
      });
    },
  });
};
</script>

<template>
  <SettingsLayout :active-tab="activeTab">
    <UForm
      :state="form"
      class="mx-auto max-w-2xl space-y-6"
      @submit.prevent="submitRule"
    >
      <!-- Card 1: Diet -->
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <h2
            class="font-display text-primary text-[24px] font-bold tracking-tight"
          >
            Dietary Preference
          </h2>
          <UButton
            icon="i-heroicons-pencil-square"
            color="gray"
            variant="ghost"
            @click="toggleEdit('dietType')"
          />
        </div>
        <UFormField :error="form.errors.dietType">
          <URadioGroup
            v-model="form.dietType"
            :items="dietOptions"
            variant="table"
            :disabled="!isEditing.dietType"
          />
        </UFormField>
      </UCard>

      <!-- Card 2: Household -->
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <h2
            class="font-display text-primary text-[24px] font-bold tracking-tight"
          >
            How many people are you cooking for?
          </h2>
          <UButton
            icon="i-heroicons-pencil-square"
            color="gray"
            variant="ghost"
            @click="toggleEdit('numberOfPeople')"
          />
        </div>
        <UFormField :error="form.errors.numberOfPeople">
          <URadioGroup
            v-model="form.numberOfPeople"
            :items="householdOptions"
            variant="table"
            :disabled="!isEditing.numberOfPeople"
          />
        </UFormField>
      </UCard>

      <!-- Card 3: Prep Time -->
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <h2
            class="font-display text-primary text-[24px] font-bold tracking-tight"
          >
            How much time do you usually have for meal prep?
          </h2>
          <UButton
            icon="i-heroicons-pencil-square"
            color="gray"
            variant="ghost"
            @click="toggleEdit('prepTime')"
          />
        </div>
        <UFormField :error="form.errors.prepTime">
          <URadioGroup
            v-model="form.prepTime"
            :items="prepTimeOptions"
            variant="table"
            :disabled="!isEditing.prepTime"
          />
        </UFormField>
      </UCard>

      <!-- Card 4: Avoided Ingredients -->
      <UCard>
        <div class="mb-6 flex items-center justify-between">
          <h2
            class="font-display text-primary text-[24px] font-bold tracking-tight"
          >
            Any ingredients we should completely avoid?
          </h2>
          <UButton
            icon="i-heroicons-pencil-square"
            color="gray"
            variant="ghost"
            @click="toggleEdit('avoidedIngredients')"
          />
        </div>
        <UFormField
          :error="form.errors.avoidedIngredients"
          hint="Separate with commas"
        >
          <UInput
            v-model="form.avoidedIngredients"
            placeholder="e.g., Peanuts, Shellfish, Cilantro"
            :disabled="!isEditing.avoidedIngredients"
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
