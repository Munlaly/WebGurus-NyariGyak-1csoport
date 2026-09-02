<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import ActionModal from '../Components/Modals/ActionModal.vue';
import { useIngredientSearch } from '../Composables/useIngredientSearch';

// --- Interfaces ---
interface Category {
  id: number;
  name: string;
  default_shelf_life_days?: number;
}

interface Ingredient {
  id: number;
  name: string;
  category?: Category;
  emoji?: string;
  base_unit?: string;
}

interface ShoppingListItem {
  id: number;
  user_id: number;
  ingredient_id: number;
  quantity: number;
  unit: string;
  is_checked: boolean;
  ingredient: Ingredient;
}

const props = defineProps<{
  items: ShoppingListItem[];
}>();

// --- Computed Properties ---
const checkedItemsCount = computed(
  () => props.items.filter((i) => i.is_checked).length,
);

// --- Item Actions ---
function toggleCheck(item: ShoppingListItem) {
  // We send a put request instantly when the checkbox is clicked
  router.put(
    route('shopping-list.update', item.id),
    { is_checked: !item.is_checked },
    { preserveScroll: true },
  );
}

function deleteItem(id: number) {
  if (confirm('Are you sure you want to remove this item?')) {
    router.delete(route('shopping-list.destroy', id), { preserveScroll: true });
  }
}

// --- Add Item Modal ---
const isAddModalOpen = ref(false);
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
      isAddModalOpen.value = false;
      addForm.reset();
      searchTerm.value = '';
    },
  });
}

// --- Finish Shopping Modal (Smart Expiration) ---
const isFinishModalOpen = ref(false);

const finishForm = useForm<{
  items: { id: number; expiration_date: string; name: string; emoji: string }[];
}>({
  items: [],
});

function openFinishModal() {
  const checkedItems = props.items.filter((i) => i.is_checked);
  if (checkedItems.length === 0) return;

  const today = new Date();

  // Populate the form with items and calculate their default expiration dates
  finishForm.items = checkedItems.map((item) => {
    const shelfLife = item.ingredient.category?.default_shelf_life_days ?? 7;

    // Calculate future date
    const expDate = new Date(today);
    expDate.setDate(today.getDate() + shelfLife);

    return {
      id: item.id,
      name: item.ingredient.name,
      emoji: item.ingredient.emoji || '🛒',
      expiration_date: expDate.toISOString().split('T')[0], // Format to YYYY-MM-DD
    };
  });

  isFinishModalOpen.value = true;
}

function submitFinish() {
  finishForm.post(route('shopping-list.finish'), {
    preserveScroll: true,
    onSuccess: () => {
      isFinishModalOpen.value = false;
    },
  });
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex flex-col gap-10">
      <!-- Header & Command Bar -->
      <section class="flex flex-col gap-6">
        <div>
          <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">
            Shopping List
          </h1>
          <p class="font-body-md text-body-md text-on-surface-variant">
            Plan your groceries and transfer them straight to your zero-waste
            inventory.
          </p>
        </div>

        <div
          class="flex flex-col items-center gap-4 sm:flex-row sm:justify-between"
        >
          <!-- Add Button -->
          <button
            class="bg-surface-container-lowest text-on-surface border-outline-variant hover:bg-surface-container-low font-body-md text-body-md relative flex shrink-0 items-center gap-2 rounded-xl border px-6 py-3 font-medium shadow-sm transition-all"
            @click="isAddModalOpen = true"
          >
            <span class="material-symbols-outlined text-[20px]">add</span>
            Add Item
          </button>

          <!-- Finish Shopping Button -->
          <button
            :disabled="checkedItemsCount === 0"
            :class="[
              checkedItemsCount > 0
                ? 'bg-primary text-on-primary shadow-sm hover:opacity-90 hover:shadow-md'
                : 'bg-surface-container text-on-surface-variant cursor-not-allowed opacity-50',
              'font-body-md text-body-md relative flex shrink-0 items-center gap-2 rounded-xl px-6 py-3 font-medium transition-all',
            ]"
            @click="openFinishModal"
          >
            <span class="material-symbols-outlined text-[20px]">task_alt</span>
            Finish Shopping ({{ checkedItemsCount }})
          </button>
        </div>
      </section>

      <!-- Checklist Area -->
      <section>
        <div
          v-if="items.length === 0"
          class="flex flex-col items-center justify-center py-12 text-center"
        >
          <span class="material-symbols-outlined text-outline mb-4 text-6xl"
            >shopping_basket</span
          >
          <h3 class="font-headline-sm text-on-surface mb-2 font-semibold">
            Your list is empty!
          </h3>
          <p class="text-on-surface-variant font-body-md">
            Add ingredients to start planning your next grocery run.
          </p>
        </div>

        <div v-else class="flex flex-col gap-3">
          <div
            v-for="item in items"
            :key="item.id"
            :class="[
              'flex items-center justify-between gap-4 rounded-xl border p-4 shadow-sm transition-all',
              item.is_checked
                ? 'bg-surface-container-lowest border-outline-variant/50 opacity-60'
                : 'bg-surface-container-lowest border-outline-variant hover:shadow-md',
            ]"
          >
            <!-- Left Side: Checkbox & Info -->
            <div class="flex items-center gap-4">
              <input
                type="checkbox"
                :checked="item.is_checked"
                class="border-outline-variant text-primary focus:ring-primary h-6 w-6 cursor-pointer rounded transition-all"
                @change="toggleCheck(item)"
              />

              <div class="flex items-center gap-3">
                <span class="text-3xl" :class="{ grayscale: item.is_checked }">
                  {{ item.ingredient.emoji || '🛒' }}
                </span>
                <div>
                  <h3
                    :class="[
                      'font-label-lg font-bold capitalize transition-all',
                      item.is_checked
                        ? 'text-on-surface-variant line-through'
                        : 'text-on-surface',
                    ]"
                  >
                    {{ item.ingredient.name }}
                  </h3>
                  <p class="font-body-sm text-on-surface-variant font-medium">
                    {{ item.quantity }} {{ item.unit }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Right Side: Delete -->
            <button
              class="text-on-surface-variant hover:bg-error-container hover:text-on-error-container flex h-10 w-10 shrink-0 items-center justify-center rounded-full transition-colors"
              title="Delete item"
              @click="deleteItem(item.id)"
            >
              <span class="material-symbols-outlined">delete</span>
            </button>
          </div>
        </div>
      </section>
    </div>

    <!-- 1. ADD ITEM MODAL -->
    <ActionModal
      :show="isAddModalOpen"
      title="Add to Shopping List"
      :processing="addForm.processing"
      submit-text="Add to List"
      submit-variant="primary"
      @close="isAddModalOpen = false"
      @submit="submitAdd"
    >
      <!-- Search Input -->
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

        <!-- Dropdown -->
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

      <!-- Quantity & Unit Grid -->
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

    <!-- 2. FINISH SHOPPING MODAL (Confirm Expiration Dates) -->
    <ActionModal
      :show="isFinishModalOpen"
      title="Confirm Expiration Dates"
      :processing="finishForm.processing"
      submit-text="Confirm & Transfer"
      submit-variant="primary"
      @close="isFinishModalOpen = false"
      @submit="submitFinish"
    >
      <p class="font-body-sm text-on-surface-variant mb-4">
        We've estimated how long these items will stay fresh based on their
        category. Please adjust any dates if necessary before adding them to
        your zero-waste inventory.
      </p>

      <div class="flex max-h-[60vh] flex-col gap-3 overflow-y-auto pr-2">
        <div
          v-for="(item, index) in finishForm.items"
          :key="item.id"
          class="bg-surface-container-lowest border-outline-variant flex items-center justify-between gap-4 rounded-xl border p-3"
        >
          <div class="flex items-center gap-3 overflow-hidden">
            <span class="shrink-0 text-2xl">{{ item.emoji }}</span>
            <span
              class="font-label-md text-on-surface truncate font-bold capitalize"
              >{{ item.name }}</span
            >
          </div>
          <div class="shrink-0">
            <input
              v-model="finishForm.items[index].expiration_date"
              type="date"
              class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary rounded-lg border p-2 text-sm font-bold transition-all focus:ring-2"
              required
            />
          </div>
        </div>
      </div>
    </ActionModal>
  </AuthenticatedLayout>
</template>
