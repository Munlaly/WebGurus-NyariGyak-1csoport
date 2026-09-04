<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';

import { ShoppingListItem } from '../Types/shoppingListInterfaces.js';

import AddItemModal from '../Components/Modals/AddItemModal.vue';
import EditItemModal from '../Components/Modals/EditItemModal.vue';
import FinishShoppingModal from '../Components/Modals/FinishShoppingModal.vue';

const props = defineProps<{
  items: ShoppingListItem[];
}>();

const checkedItems = computed(() => props.items.filter((i) => i.is_checked));
const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isFinishModalOpen = ref(false);
const editingItem = ref<ShoppingListItem | null>(null);

function toggleCheck(item: ShoppingListItem) {
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

function openEditModal(item: ShoppingListItem) {
  editingItem.value = item;
  isEditModalOpen.value = true;
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
            :disabled="checkedItems.length === 0"
            :class="[
              checkedItems.length > 0
                ? 'bg-primary text-on-primary shadow-sm hover:opacity-90 hover:shadow-md'
                : 'bg-surface-container text-on-surface-variant cursor-not-allowed opacity-50',
              'font-body-md text-body-md relative flex shrink-0 items-center gap-2 rounded-xl px-6 py-3 font-medium transition-all',
            ]"
            @click="isFinishModalOpen = true"
          >
            <span class="material-symbols-outlined text-[20px]">task_alt</span>
            Finish Shopping ({{ checkedItems.length }})
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

            <!-- Right Side: Actions -->
            <div class="flex shrink-0 items-center gap-1">
              <button
                class="text-on-surface-variant hover:bg-surface-container-high hover:text-primary flex h-10 w-10 items-center justify-center rounded-full transition-colors"
                title="Edit item"
                @click="openEditModal(item)"
              >
                <span class="material-symbols-outlined text-[20px]">edit</span>
              </button>

              <button
                class="text-on-surface-variant hover:bg-error-container hover:text-on-error-container flex h-10 w-10 items-center justify-center rounded-full transition-colors"
                title="Delete item"
                @click="deleteItem(item.id)"
              >
                <span class="material-symbols-outlined text-[20px]"
                  >delete</span
                >
              </button>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- 3. Modals rendered via Components! -->
    <AddItemModal :show="isAddModalOpen" @close="isAddModalOpen = false" />

    <EditItemModal
      :show="isEditModalOpen"
      :item="editingItem"
      @close="isEditModalOpen = false"
    />

    <FinishShoppingModal
      :show="isFinishModalOpen"
      :checked-items="checkedItems"
      @close="isFinishModalOpen = false"
    />
  </AuthenticatedLayout>
</template>
