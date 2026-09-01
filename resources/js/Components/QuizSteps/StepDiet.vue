<script setup lang="ts">
import { computed, watch } from 'vue';

const props = defineProps<{
  options: {
    id: number;
    name: string;
    description: string | null;
  }[];
  baseDietIds: number[];
}>();

const emit = defineEmits(['update:isValid']);

const model = defineModel<number[]>({ required: true });

const hasConflict = computed(() => {
  const selectedBaseDiets = model.value.filter((id) =>
    props.baseDietIds.includes(id),
  );
  return selectedBaseDiets.length > 1;
});

//
const dietaryItems = computed(() => {
  return props.options.map((diet) => {
    const isBaseDiet = props.baseDietIds.includes(diet.id);
    const isSelected = model.value.includes(diet.id);
    const isConflictingCard = hasConflict.value && isBaseDiet && isSelected;

    return {
      value: String(diet.id),
      label: diet.name,
      description: diet.description || undefined,
      class: isConflictingCard
        ? '!ring-0 !border-2 !border-red-500 bg-red-50'
        : '',
    };
  });
});

// Converts UI's string array to number array
const stringModel = computed({
  get: () => model.value.map(String),
  set: (val: string[]) => {
    model.value = val.map(Number);
  },
});

watch(
  hasConflict,
  (conflict) => {
    emit('update:isValid', !conflict);
  },
  { immediate: true },
);
</script>

<template>
  <div
    class="flex w-full max-w-2xl flex-col items-center space-y-10 text-center"
  >
    <div class="space-y-4">
      <h2
        class="font-display text-on-surface text-3xl font-bold tracking-tight text-slate-900"
      >
        Dietary Preferences
      </h2>
      <p class="text-lg leading-relaxed text-slate-700">
        Do you follow any specific diets? Select all that apply so we can filter
        your meal plan accordingly.
      </p>
    </div>

    <div class="w-full max-w-md text-left">
      <UFormField
        name="meal_plan_preferences"
        :error="
          hasConflict
            ? 'You cannot select conflicting baseline diets (e.g., Vegetarian and Omnivore). Please select only one.'
            : undefined
        "
      >
        <div class="mt-2 space-y-4">
          <UCheckboxGroup
            v-model="stringModel"
            :items="dietaryItems"
            size="lg"
            class="mt-2"
            variant="card"
            color="primary"
            :ui="{
              label: 'text-slate-900  font-semibold',
              description: 'text-slate-700  text-sm',
              item: 'mt-2 border-stone-400',
            }"
          />
        </div>
      </UFormField>
    </div>
  </div>
</template>
