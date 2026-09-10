<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import ActionModal from '../Components/Modals/ActionModal.vue';
import { InventoryItem } from '../Types/inventoryInterfaces';
import AddItemModal from '../Components/Modals/AddItemModal.vue';
import {
  getStatusLabel,
  getItemState,
  getCategoryEmoji,
} from '../utils/inventory';
import { useUnits } from '../Composables/useUnits.js';
import { useQuantityAction } from '../Composables/useQuantityAction.js';
import QuantityUpdateModal from '../Components/Modals/QuantityUpdateModal.vue';

const props = defineProps<{
  attentionNeeded: InventoryItem[];
  inventory: InventoryItem[];
  currentScore: number;
}>();

const { formatQuantity } = useUnits();

const {
  modal: decreaseModal,
  displayAmount: decreaseDisplayAmount,
  itemUnit: decreaseItemUnit,
  openModal: openDecreaseModal,
} = useQuantityAction('inventory.decrease', 'amount_to_remove');

const {
  modal: increaseModal,
  displayAmount: increaseDisplayAmount,
  itemUnit: increaseItemUnit,
  openModal: openIncreaseModal,
} = useQuantityAction('inventory.increase', 'amount_to_add');

const categories = [
  'All',
  'Produce',
  'Meat',
  'Seafood',
  'Milk, Eggs, other Dairy',
  'Cheese',
  'Pasta and Rice',
];

const searchQuery = ref('');
const selectedCategory = ref('All');
const isDeleteModalOpen = ref(false);
const itemToDelete = ref<InventoryItem | null>(null);

const showAddItemModal = ref(false);

const filteredInventory = computed(() => {
  return props.inventory.filter((item) => {
    const matchesSearch = item.ingredient.name
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());

    const itemCategoryName = item.ingredient.category?.name || '';

    const matchesCategory =
      selectedCategory.value === 'All' ||
      itemCategoryName.toLowerCase() === selectedCategory.value.toLowerCase();

    return matchesSearch && matchesCategory;
  });
});

function promptDelete(item: InventoryItem) {
  itemToDelete.value = item;
  isDeleteModalOpen.value = true;
}

function executeDelete() {
  if (itemToDelete.value !== null) {
    router.delete(route('inventory.destroy', itemToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        isDeleteModalOpen.value = false;
        itemToDelete.value = null;
      },
    });
  }
}

function scrollToItem(id: number) {
  const element = document.getElementById(`inventory-item-${id}`);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });

    element.classList.add(
      'ring-2',
      'ring-primary',
      'dark:ring-white/30',
      'transition-all',
    );

    setTimeout(() => {
      element.classList.remove('ring-2', 'ring-primary', 'dark:ring-white/30');
    }, 2000);
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="flex flex-col gap-10">
      <!-- Header & Command Bar -->
      <section class="flex flex-col gap-6">
        <div>
          <h1 class="font-headline-lg text-headline-lg text-on-surface mb-2">
            My Inventory
          </h1>
          <p class="font-body-md text-body-md text-on-surface-variant">
            Manage your pantry and reduce waste.
            {{ inventory.length }} items currently tracked.
          </p>
        </div>
        <div class="flex flex-col items-center gap-4 sm:flex-row">
          <div class="relative w-full sm:max-w-md">
            <span
              class="material-symbols-outlined text-on-surface-variant pointer-events-none absolute top-1/2 left-4 -translate-y-1/2"
              >search</span
            >
            <input
              v-model="searchQuery"
              class="bg-surface-container-low focus:ring-primary text-on-surface placeholder:text-on-surface-variant/60 font-body-md text-body-md w-full rounded-xl border-none py-3 pr-4 pl-12 shadow-inner transition-shadow focus:ring-2"
              placeholder="Search to add or update ingredients..."
              type="text"
            />
          </div>
          <button
            class="bg-primary text-on-primary font-body-md text-body-md relative flex shrink-0 items-center gap-2 rounded-xl px-6 py-3 font-medium shadow-sm transition-opacity hover:opacity-90 hover:shadow-md"
            @click="showAddItemModal = true"
          >
            <span class="material-symbols-outlined text-[20px]"
              >shopping_cart</span
            >
            Add to Shopping List
          </button>
        </div>
      </section>

      <section v-if="attentionNeeded.length > 0" class="flex flex-col gap-4">
        <h2
          class="font-headline-md text-headline-md text-error flex items-center gap-2"
        >
          <span class="material-symbols-outlined text-[28px] drop-shadow-sm"
            >warning</span
          >
          Attention Needed (Expiring/Low)
        </h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="item in attentionNeeded"
            :key="item.id"
            class="border-surface-variant/50 bg-surface-container-lowest hover:bg-surface-container-low flex cursor-pointer items-start gap-3 rounded-xl border p-4 shadow-sm transition-all hover:scale-[1.01]"
            @click="scrollToItem(item.id)"
          >
            <div class="mt-1 text-3xl leading-none">
              {{
                item.ingredient.emoji ||
                getCategoryEmoji(item.ingredient.category?.name)
              }}
            </div>
            <div>
              <h3
                class="font-label-md text-label-md text-on-surface font-bold capitalize"
              >
                {{ item.ingredient.name }}
              </h3>
              <p
                class="font-body-sm text-body-sm text-error mt-0.5 font-medium"
              >
                {{ getItemState(item).expText || getStatusLabel(item.status) }}
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Category Tabs -->
      <section
        class="no-scrollbar -mx-4 overflow-x-auto px-4 pb-2 sm:mx-0 sm:px-0"
      >
        <div class="flex min-w-max gap-2">
          <button
            v-for="cat in categories"
            :key="cat"
            :class="[
              selectedCategory === cat
                ? 'bg-primary text-on-primary hover:bg-primary/90 font-bold shadow-md dark:hover:bg-[#b080ea]'
                : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant hover:bg-surface-container-low border font-medium shadow-sm',
              'font-label-md text-label-md rounded-full px-5 py-2 transition-colors',
            ]"
            @click="selectedCategory = cat"
          >
            {{ cat }}
          </button>
        </div>
      </section>

      <!-- Inventory Grid -->
      <section>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-6">
          <div
            v-for="item in filteredInventory"
            :id="`inventory-item-${item.id}`"
            :key="item.id"
            :class="[
              'group relative flex flex-col items-center overflow-hidden rounded-xl border p-4 text-center shadow-[0px_4px_20px_rgba(0,0,0,0.04)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0px_10px_30px_rgba(0,0,0,0.08)]',
              getItemState(item).cardClass,
            ]"
          >
            <div
              :class="[
                'mt-2 mb-3 flex items-center justify-center rounded-full p-3 text-4xl transition-transform duration-300 group-hover:scale-110',
                getItemState(item).iconClass,
              ]"
            >
              {{
                item.ingredient.emoji ||
                getCategoryEmoji(item.ingredient.category?.name)
              }}
            </div>
            <h3
              class="font-body-sm text-body-sm text-on-surface mb-1 line-clamp-1 font-semibold capitalize"
            >
              {{ item.ingredient.name }}
            </h3>

            <!-- Dual Text Badges -->
            <div
              class="mt-auto flex w-full flex-col gap-1 pt-2 transition-opacity duration-200 group-hover:pointer-events-none group-hover:opacity-0"
            >
              <span
                :class="[
                  'inline-block rounded-md px-2 py-0.5 text-[10px] font-bold',
                  getItemState(item).badgeClass,
                ]"
              >
                {{ formatQuantity(item.amount_left, item.unit) }} •
                {{ item.status }}
              </span>
              <span
                v-if="getItemState(item).expText"
                :class="[
                  'inline-block rounded-md px-2 py-0.5 text-[10px] font-bold',
                  getItemState(item).badgeClass,
                ]"
              >
                {{ getItemState(item).expText }}
              </span>
            </div>

            <!-- Hover State: Actions -->
            <div
              class="absolute bottom-4 left-0 flex w-full translate-y-4 justify-center gap-2 px-2 opacity-0 transition-all duration-200 group-hover:translate-y-0 group-hover:opacity-100"
            >
              <!-- Decrease Quantity Button -->
              <button
                class="bg-surface-container-high text-on-surface hover:bg-surface-variant flex h-8 w-8 items-center justify-center rounded-full shadow-sm transition-colors"
                title="Decrease quantity"
                @click.stop="openDecreaseModal(item)"
              >
                <span class="material-symbols-outlined text-sm">remove</span>
              </button>

              <!-- Increase Quantity Button -->
              <button
                class="bg-surface-container-high text-on-surface hover:bg-surface-variant flex h-8 w-8 items-center justify-center rounded-full shadow-sm transition-colors"
                title="Add new batch"
                @click.stop="openIncreaseModal(item)"
              >
                <span class="material-symbols-outlined text-sm">add</span>
              </button>

              <!-- Delete Item Button -->
              <button
                class="bg-error-container text-on-error-container hover:bg-error hover:text-on-error ml-1 flex h-8 w-8 items-center justify-center rounded-full shadow-sm transition-colors"
                title="Delete item"
                @click.stop="promptDelete(item)"
              >
                <span class="material-symbols-outlined text-sm">delete</span>
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>
    <!-- Shopping List Modal -->
    <ActionModal
      :show="shoppingModal.isOpen"
      title="Add to Shopping List"
      :processing="shoppingModal.form.processing"
      submit-text="Add Item"
      submit-variant="primary"
      @close="shoppingModal.isOpen = false"
      @submit="shoppingModal.submit"
    >
      <div
        v-if="shoppingModal.selectedItem"
        class="bg-surface-container-lowest border-outline-variant/30 mb-2 flex items-center gap-4 rounded-xl border p-4 shadow-inner"
      >
        <span class="text-4xl">{{
          shoppingModal.selectedItem.emoji ||
          getCategoryEmoji((shoppingModal.selectedItem as any).category?.name)
        }}</span>
        <span class="font-label-lg text-on-surface font-bold capitalize">
          {{ shoppingModal.selectedItem.name }}
        </span>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label
            class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
            >Quantity</label
          >
          <input
            v-model="shoppingModal.form.quantity"
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
            v-model="shoppingModal.form.unit"
            class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
          >
            <option value="pcs">Pieces</option>
            <option value="g">Grams</option>
            <option value="kg">Kilos</option>
            <option value="ml">mL</option>
            <option value="l">Liters</option>
          </select>
        </div>
      </div>
    </ActionModal>

    <!-- Decrease Quantity Modal -->
    <QuantityUpdateModal
      v-model:display-amount="decreaseDisplayAmount"
      :modal-state="decreaseModal"
      :item-unit="decreaseItemUnit"
      title="Decrease Quantity"
      submit-text="Remove"
      submit-variant="error"
      input-label="Amount to remove"
      @close="decreaseModal.isOpen = false"
      @submit="decreaseModal.submit"
    />

    <!-- Increase Quantity Modal -->
    <QuantityUpdateModal
      v-model:display-amount="increaseDisplayAmount"
      :modal-state="increaseModal"
      :item-unit="increaseItemUnit"
      title="Increase Quantity"
      submit-text="Add"
      submit-variant="primary"
      input-label="Amount to add"
      @close="increaseModal.isOpen = false"
      @submit="increaseModal.submit"
    />

    <!-- Delete Item Confirmation Modal -->
    <ActionModal
      :show="isDeleteModalOpen"
      title="Remove Item"
      submit-text="Delete"
      submit-variant="error"
      @close="isDeleteModalOpen = false"
      @submit="executeDelete"
    >
      <div
        v-if="itemToDelete"
        class="bg-surface-container-lowest border-outline-variant/30 mb-4 flex items-center gap-4 rounded-xl border p-4 shadow-inner"
      >
        <span class="text-4xl">{{
          itemToDelete.ingredient.emoji ||
          getCategoryEmoji(itemToDelete.ingredient.category?.name)
        }}</span>
        <div>
          <span
            class="font-label-lg text-on-surface block font-bold capitalize"
          >
            {{ itemToDelete.ingredient.name }}
          </span>
          <span class="font-body-sm text-on-surface-variant">
            Current:
            {{
              formatQuantity(
                itemToDelete.amount_left,
                itemToDelete.unit || itemToDelete.ingredient.base_unit || '',
              )
            }}
          </span>
        </div>
      </div>
      <p class="font-body-md text-on-surface-variant">
        Are you sure you want to remove this item from your inventory?
      </p>
    </ActionModal>

    <AddItemModal :show="showAddItemModal" @close="showAddItemModal = false" />
    <!-- Delete Item Confirmation Modal -->
    <ActionModal
      :show="isDeleteModalOpen"
      title="Remove Item"
      submit-text="Delete"
      submit-variant="error"
      @close="isDeleteModalOpen = false"
      @submit="executeDelete"
    >
      <div
        v-if="itemToDelete"
        class="bg-surface-container-lowest border-outline-variant/30 mb-4 flex items-center gap-4 rounded-xl border p-4 shadow-inner"
      >
        <span class="text-4xl">{{
          itemToDelete.ingredient.emoji ||
          getCategoryEmoji(itemToDelete.ingredient.category?.name)
        }}</span>
        <div>
          <span
            class="font-label-lg text-on-surface block font-bold capitalize"
          >
            {{ itemToDelete.ingredient.name }}
          </span>
          <span class="font-body-sm text-on-surface-variant">
            Current:
            {{
              formatQuantity(
                itemToDelete.amount_left,
                itemToDelete.unit || itemToDelete.ingredient.base_unit || '',
              )
            }}
          </span>
        </div>
      </div>

      <p class="font-body-md text-on-surface-variant">
        Are you sure you want to remove this item from your inventory?
      </p>
    </ActionModal>

    <AddInventoryModal ref="addInventoryModalRef" />
  </AuthenticatedLayout>
</template>
