<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';
import { useActionModal } from '../Composables/useActionModal';

import {
  Ingredient,
  InventoryItem,
  getStatusLabel,
  getItemState,
} from '../utils/inventory';

const props = defineProps<{
  attentionNeeded: InventoryItem[];
  inventory: InventoryItem[];
  currentScore: number;
}>();

const searchQuery = ref('');
const selectedCategory = ref('All');
const categories = ['All', 'Produce', 'Meat & Fish', 'Dairy', 'Dry Goods'];

const shoppingModal = useActionModal<
  Ingredient,
  { ingredient_id: number | null; quantity: number; unit: string }
>(
  () => route('shopping-list.store'),
  {
    ingredient_id: null,
    quantity: 1,
    unit: 'pcs',
  },
  'post',
);

const decreaseModal = useActionModal<
  InventoryItem,
  { amount_to_remove: number }
>(
  (item) => route('inventory.decrease', item.id),
  {
    amount_to_remove: 1,
  },
  'put',
);

const filteredInventory = computed(() => {
  return props.inventory.filter((item) => {
    return item.ingredient.name
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());
  });
});

const deleteItem = (id: number) => {
  if (confirm('Are you sure you want to remove this item?')) {
    router.delete(route('inventory.destroy', id), { preserveScroll: true });
  }
};

function scrollToItem(id: number) {
  const element = document.getElementById(`inventory-item-${id}`);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    element.classList.add('ring-2', 'ring-primary', 'transition-all');
    setTimeout(() => {
      element.classList.remove('ring-2', 'ring-primary');
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
            {{ inventory.length + attentionNeeded.length }} items currently
            tracked.
          </p>
        </div>
        <div class="flex flex-col items-center gap-4 sm:flex-row">
          <div class="relative w-full grow">
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
          >
            <span class="material-symbols-outlined text-[20px]"
              >fact_check</span
            >
            Micro-Inventory Check
            <span
              v-if="attentionNeeded.length > 0"
              class="bg-error border-background absolute -top-1 -right-1 h-3.5 w-3.5 rounded-full border-2"
            ></span>
          </button>
        </div>
      </section>

      <!-- ZeroWaste Alert Zone (Clickable to jump down) -->
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
              {{ item.ingredient.emoji || '📦' }}
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
                ? 'bg-primary text-on-primary hover:bg-surface-tint font-bold shadow-md'
                : 'bg-surface-container-lowest text-on-surface-variant border-outline-variant hover:bg-surface-container-low border font-medium shadow-sm',
              'font-label-md text-label-md rounded-full px-5 py-2 transition-colors',
            ]"
            @click="selectedCategory = cat"
          >
            {{ cat }}
          </button>
        </div>
      </section>

      <!-- Inventory Grid (With unique IDs for jumping) -->
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
              {{ item.ingredient.emoji || '📦' }}
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
                {{ getItemState(item).statusText }}
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
                @click.stop="decreaseModal.open(item, { amount_to_remove: 1 })"
              >
                <span class="material-symbols-outlined text-sm">remove</span>
              </button>

              <!-- Add to Shopping List Button -->
              <button
                class="bg-surface-container-high text-on-surface hover:bg-surface-variant flex h-8 w-8 items-center justify-center rounded-full shadow-sm transition-colors"
                title="Add to Shopping List"
                @click.stop="
                  shoppingModal.open(item.ingredient, {
                    ingredient_id: item.ingredient.id,
                    quantity: 1,
                  })
                "
              >
                <span class="material-symbols-outlined text-sm">add</span>
              </button>

              <!-- Delete Item Button -->
              <button
                class="bg-error-container text-on-error-container hover:bg-error hover:text-on-error ml-1 flex h-8 w-8 items-center justify-center rounded-full shadow-sm transition-colors"
                title="Delete item"
                @click.stop="deleteItem(item.id)"
              >
                <span class="material-symbols-outlined text-sm">delete</span>
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>
    <!-- Shopping List Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="shoppingModal.isOpen"
        class="fixed inset-0 z-100 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        @click.self="shoppingModal.isOpen = false"
      >
        <div
          class="bg-surface border-outline-variant w-full max-w-sm rounded-2xl border p-6 shadow-2xl dark:bg-gray-900"
        >
          <div class="mb-6 flex items-center justify-between">
            <h2
              class="font-headline-sm text-headline-sm text-on-surface font-bold"
            >
              Add to Shopping List
            </h2>
            <button
              class="text-on-surface-variant hover:text-on-surface transition-colors"
              @click="shoppingModal.isOpen = false"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div
            v-if="shoppingModal.selectedItem"
            class="bg-surface-container-lowest border-outline-variant/30 mb-6 flex items-center gap-4 rounded-xl border p-4 shadow-inner"
          >
            <span class="text-4xl">{{
              shoppingModal.selectedItem.emoji || '📦'
            }}</span>
            <span class="font-label-lg text-on-surface font-bold capitalize">{{
              shoppingModal.selectedItem.name
            }}</span>
          </div>

          <form
            class="flex flex-col gap-5"
            @submit.prevent="shoppingModal.submit"
          >
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

            <div class="mt-2 flex gap-3">
              <button
                type="button"
                class="bg-surface-container-high text-on-surface hover:bg-surface-variant flex-1 rounded-xl py-3 font-bold transition-colors active:scale-95"
                @click="shoppingModal.isOpen = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="shoppingModal.form.processing"
                class="bg-primary text-on-primary flex-1 rounded-xl py-3 font-bold shadow-sm transition-all hover:opacity-90 active:scale-95 disabled:opacity-50"
              >
                Add Item
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Decrease Quantity Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="decreaseModal.isOpen"
        class="fixed inset-0 z-100 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        @click.self="decreaseModal.isOpen = false"
      >
        <div
          class="bg-surface border-outline-variant w-full max-w-sm rounded-2xl border p-6 shadow-2xl dark:bg-gray-900"
        >
          <div class="mb-6 flex items-center justify-between">
            <h2
              class="font-headline-sm text-headline-sm text-on-surface font-bold"
            >
              Decrease Quantity
            </h2>
            <button
              class="text-on-surface-variant hover:text-on-surface transition-colors"
              @click="decreaseModal.isOpen = false"
            >
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div
            v-if="decreaseModal.selectedItem"
            class="bg-surface-container-lowest border-outline-variant/30 mb-6 flex items-center gap-4 rounded-xl border p-4 shadow-inner"
          >
            <span class="text-4xl">{{
              decreaseModal.selectedItem.ingredient.emoji || '📦'
            }}</span>
            <div>
              <span
                class="font-label-lg text-on-surface block font-bold capitalize"
                >{{ decreaseModal.selectedItem.ingredient.name }}</span
              >
              <span class="font-body-sm text-on-surface-variant"
                >Current: {{ decreaseModal.selectedItem.amount_left }}
                {{ decreaseModal.selectedItem.ingredient.base_unit }}</span
              >
            </div>
          </div>

          <form
            class="flex flex-col gap-5"
            @submit.prevent="decreaseModal.submit"
          >
            <div>
              <label
                class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
                >Amount to remove</label
              >
              <input
                v-model="decreaseModal.form.amount_to_remove"
                type="number"
                min="0.1"
                step="0.1"
                class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
                required
              />
            </div>

            <div class="mt-2 flex gap-3">
              <button
                type="button"
                class="bg-surface-container-high text-on-surface hover:bg-surface-variant flex-1 rounded-xl py-3 font-bold transition-colors active:scale-95"
                @click="decreaseModal.isOpen = false"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="decreaseModal.form.processing"
                class="bg-error text-on-error flex-1 rounded-xl py-3 font-bold shadow-sm transition-all hover:opacity-90 active:scale-95 disabled:opacity-50"
              >
                Remove
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </AuthenticatedLayout>
</template>
