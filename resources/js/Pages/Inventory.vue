<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout.vue';

interface Ingredient {
  id: number;
  name: string;
  category?: string;
  base_unit?: string;
  emoji?: string;
}

interface InventoryItem {
  id: number;
  user_id: number;
  ingredient_id: number;
  amount_left: number | null;
  status: 'FULL' | 'OPENED' | 'LOW';
  expiration_date: string | null;
  is_frozen: boolean;
  ingredient: Ingredient;
}

const props = defineProps<{
  attentionNeeded: InventoryItem[];
  inventory: InventoryItem[];
  currentScore: number;
}>();

const getStatusLabel = (status: 'FULL' | 'OPENED' | 'LOW') => {
  switch (status) {
    case 'LOW':
      return 'Low Stock';
    case 'OPENED':
      return 'Opened';
    case 'FULL':
    default:
      return 'Full';
  }
};

const getDiffDays = (dateStr: string | null) => {
  if (!dateStr) return null;
  const [datePart] = dateStr.split('T');
  const [y, m, d] = datePart.split('-').map(Number);
  const exp = new Date(y, m - 1, d).getTime();

  const nowObj = new Date();
  const now = new Date(
    nowObj.getFullYear(),
    nowObj.getMonth(),
    nowObj.getDate(),
  ).getTime();

  return Math.round((exp - now) / (1000 * 60 * 60 * 24));
};

const getItemState = (item: InventoryItem) => {
  const diffDays = getDiffDays(item.expiration_date);
  const unit = item.ingredient?.base_unit || 'units';
  const qtyText = `${item.amount_left ?? 0} ${unit}`;

  const baseCardClass = 'border-surface-variant/40 bg-surface-container-lowest';

  if (diffDays !== null && diffDays < 0) {
    return {
      statusText: `${qtyText} • ${getStatusLabel(item.status)}`,
      expText: 'Expired',
      cardClass: baseCardClass,
      iconClass:
        'bg-red-50 text-red-700 dark:bg-rose-950/60 dark:text-rose-300 dark:border dark:border-rose-900/40',
      badgeClass:
        'bg-red-50 text-red-700 dark:bg-rose-950/60 dark:text-rose-300',
    };
  }

  if (item.status === 'LOW' || (diffDays !== null && diffDays <= 1)) {
    const expText =
      diffDays === 0
        ? 'Expiring today'
        : diffDays === 1
          ? 'Expiring tomorrow'
          : 'Critical';
    return {
      statusText: `${qtyText} • ${getStatusLabel(item.status)}`,
      expText: expText,
      cardClass: baseCardClass,
      iconClass:
        'bg-orange-50 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300 dark:border dark:border-orange-900/40',
      badgeClass:
        'bg-orange-50 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300',
    };
  }

  if (item.status === 'OPENED' || (diffDays !== null && diffDays <= 7)) {
    const expText =
      diffDays !== null ? `Expiring in ${diffDays} days` : 'Urgent';
    return {
      statusText: `${qtyText} • ${getStatusLabel(item.status)}`,
      expText: expText,
      cardClass: baseCardClass,
      iconClass:
        'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 dark:border dark:border-amber-900/40',
      badgeClass:
        'bg-amber-50 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
    };
  }

  return {
    statusText: `${qtyText} • ${getStatusLabel(item.status)}`,
    expText: null,
    cardClass: baseCardClass,
    iconClass:
      'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border dark:border-emerald-900/40',
    badgeClass:
      'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300',
  };
};

const searchQuery = ref('');
const selectedCategory = ref('All');
const categories = ['All', 'Produce', 'Meat & Fish', 'Dairy', 'Dry Goods'];

const filteredInventory = computed(() => {
  return props.inventory.filter((item) => {
    return item.ingredient.name
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());
  });
});

const updateQuantity = (item: InventoryItem, delta: number) => {
  const newAmount = (item.amount_left ?? 0) + delta;
  if (newAmount < 0) return;

  router.put(
    route('inventory.update', item.id),
    {
      amount_left: newAmount,
      status: newAmount === 0 ? 'LOW' : item.status,
    },
    { preserveScroll: true },
  );
};

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
            class="bg-tertiary-fixed text-on-tertiary-container font-body-md text-body-md relative flex shrink-0 items-center gap-2 rounded-xl px-6 py-3 font-medium shadow-sm transition-opacity hover:opacity-90 hover:shadow-md"
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

            <!-- Hover State: Actions (Only + and Delete) -->
            <div
              class="absolute bottom-4 left-0 flex w-full translate-y-4 justify-center gap-2 px-2 opacity-0 transition-all duration-200 group-hover:translate-y-0 group-hover:opacity-100"
            >
              <button
                class="bg-surface-container-high text-on-surface hover:bg-surface-variant flex h-8 w-8 items-center justify-center rounded-full shadow-sm transition-colors"
                title="Add quantity"
                @click.stop="updateQuantity(item, 1)"
              >
                <span class="material-symbols-outlined text-sm">add</span>
              </button>
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
  </AuthenticatedLayout>
</template>
