<script setup lang="ts">
import { useActionModal } from '../Composables/useActionModal';
import ActionModal from './ActionModal.vue';
import { useIngredientSearch } from '../Composables/useIngredientSearch';

const { searchTerm, searchResults, isSearchLoading, isDropdownOpen } =
  useIngredientSearch();

const addModal = useActionModal<
  null,
  {
    ingredient_id: number | null;
    amount_left: number;
    unit: string;
    status: 'FULL' | 'OPENED' | 'LOW';
    expiration_date: string;
    is_frozen: boolean;
  }
>(
  () => route('inventory.store'),
  {
    ingredient_id: null,
    amount_left: 1,
    unit: 'pcs',
    status: 'FULL',
    expiration_date: '',
    is_frozen: false,
  },
  'post',
);

function selectIngredient(ingredient: { id: number; name: string }) {
  addModal.form.ingredient_id = ingredient.id;
  searchTerm.value = ingredient.name;
  isDropdownOpen.value = false;
}

defineExpose({
  open: () => addModal.open(null),
});
</script>

<template>
  <ActionModal
    :show="addModal.isOpen"
    title="Add to Inventory"
    :processing="addModal.form.processing"
    submit-text="Add Item"
    submit-variant="primary"
    @close="addModal.isOpen = false"
    @submit="addModal.submit"
  >
    <!-- Custom Autocomplete Search -->
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
        <span
          v-if="isSearchLoading"
          class="absolute top-3 right-3 text-sm text-gray-400"
        >
          <span class="material-symbols-outlined animate-spin"
            >progress_activity</span
          >
        </span>
      </div>

      <!-- Dropdown Menu -->
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
      <div
        v-else-if="isDropdownOpen && !isSearchLoading && searchTerm"
        class="bg-surface-container-lowest border-outline-variant absolute mt-1 w-full rounded-xl border p-3 text-sm text-gray-500 shadow-lg"
      >
        No ingredients found.
      </div>
    </div>

    <!-- Amount and Unit Grid -->
    <div class="mt-4 grid grid-cols-2 gap-4">
      <div>
        <label
          class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
          >Initial Amount</label
        >
        <input
          v-model="addModal.form.amount_left"
          type="number"
          min="0"
          step="0.1"
          class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
        />
      </div>
      <div>
        <label
          class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
          >Unit</label
        >
        <select
          v-model="addModal.form.unit"
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

    <!-- Status & Expiration Grid -->
    <div class="mt-4 grid grid-cols-2 gap-4">
      <div>
        <label
          class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
          >Status</label
        >
        <select
          v-model="addModal.form.status"
          class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
        >
          <option value="FULL">Full</option>
          <option value="OPENED">Opened</option>
          <option value="LOW">Low</option>
        </select>
      </div>
      <div>
        <label
          class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
          >Expiration Date</label
        >
        <input
          v-model="addModal.form.expiration_date"
          type="date"
          class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
        />
      </div>
    </div>

    <div class="mt-2 flex items-center gap-3 pt-2">
      <input
        id="is_frozen"
        v-model="addModal.form.is_frozen"
        type="checkbox"
        class="border-outline-variant text-primary focus:ring-primary h-5 w-5 rounded"
      />
      <label for="is_frozen" class="font-label-md text-on-surface font-medium"
        >Is Frozen?</label
      >
    </div>
  </ActionModal>
</template>
