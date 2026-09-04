<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import ActionModal from '../../Components/Modals/ActionModal.vue';
import { useIngredientSearch } from '../../Composables/useIngredientSearch.js';

defineProps<{ show: boolean }>();
const emit = defineEmits(['close']);

const { searchTerm, searchResults, isSearchLoading, isDropdownOpen } =
  useIngredientSearch();

const addForm = useForm({
  ingredient_id: null as number | null,
  quantity: 1,
  unit: 'pcs',
});

function selectIngredient(ingredient: { id: number; name: string }) {
  addForm.ingredient_id = ingredient.id;
  searchTerm.value = ingredient.name;
  isDropdownOpen.value = false;
}

function submitAdd() {
  addForm.post(route('shopping-list.store'), {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
      addForm.reset();
      searchTerm.value = '';
    },
  });
}
</script>

<template>
  <ActionModal
    :show="show"
    title="Add to Shopping List"
    :processing="addForm.processing"
    submit-text="Add to List"
    submit-variant="primary"
    :submit-disabled="!addForm.ingredient_id"
    @close="emit('close')"
    @submit="submitAdd"
  >
    <div class="relative z-50">
      <label
        class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
        >Search Ingredient</label
      >
      <div class="relative">
        <input
          v-model="searchTerm"
          type="text"
          class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
          placeholder="e.g., Avocado..."
          required
          @focus="searchTerm.length > 0 ? (isDropdownOpen = true) : null"
        />
        <div
          v-if="addForm.errors.ingredient_id"
          class="text-error mt-1 text-xs font-medium"
        >
          Please select an ingredient from the dropdown.
        </div>
        <span
          v-if="isSearchLoading"
          class="absolute top-3 right-3 text-sm text-gray-400"
        >
          <span class="material-symbols-outlined animate-spin"
            >progress_activity</span
          >
        </span>
      </div>

      <ul
        v-if="isDropdownOpen && searchResults.length > 0"
        class="bg-surface-container-lowest border-outline-variant absolute mt-1 max-h-48 w-full overflow-y-auto rounded-xl border shadow-lg"
      >
        <li
          v-for="ing in searchResults"
          :key="ing.id"
          class="hover:bg-surface-container-low text-on-surface cursor-pointer p-3 font-medium capitalize transition-colors"
          @click="selectIngredient(ing)"
        >
          {{ ing.name }}
        </li>
      </ul>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-4">
      <div>
        <label
          class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
          >Quantity</label
        >
        <input
          v-model="addForm.quantity"
          type="number"
          min="0.1"
          step="0.1"
          class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
          required
        />
      </div>
      <div>
        <label
          class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
          >Unit</label
        >
        <select
          v-model="addForm.unit"
          class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
        >
          <option value="pcs">Pieces (pcs)</option>
          <option value="g">Grams (g)</option>
          <option value="kg">Kilos (kg)</option>
          <option value="ml">Milliliters (ml)</option>
          <option value="l">Liters (l)</option>
        </select>
      </div>
    </div>
  </ActionModal>
</template>
