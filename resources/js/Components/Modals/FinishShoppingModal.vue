<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionModal from '../../Components/Modals/ActionModal.vue';
import { ShoppingListItem } from '../../Types/shoppingListInterfaces.js';

const props = defineProps<{
  show: boolean;
  checkedItems: ShoppingListItem[];
}>();

const emit = defineEmits(['close']);

const finishForm = useForm<{
  items: { id: number; expiration_date: string; name: string; emoji: string }[];
}>({
  items: [],
});

// Recalculate dates every time the modal is opened
watch(
  () => props.show,
  (isShowing) => {
    if (isShowing && props.checkedItems.length > 0) {
      const today = new Date();

      finishForm.items = props.checkedItems.map((item) => {
        const shelfLife =
          item.ingredient.category?.default_shelf_life_days ?? 7;
        const expDate = new Date(today);
        expDate.setDate(today.getDate() + shelfLife);

        // Local date for saving and zero waste pueposes
        const offset = expDate.getTimezoneOffset() * 60000;
        const localISODate = new Date(expDate.getTime() - offset)
          .toISOString()
          .split('T')[0];

        return {
          id: item.id,
          name: item.ingredient.name,
          emoji: item.ingredient.emoji || '🛒',
          expiration_date: localISODate,
        };
      });
    }
  },
);

function submitFinish() {
  finishForm.post(route('shopping-list.finish'), {
    preserveScroll: true,
    onSuccess: () => emit('close'),
  });
}
</script>

<template>
  <ActionModal
    :show="show"
    title="Confirm Expiration Dates"
    :processing="finishForm.processing"
    submit-text="Confirm & Transfer"
    submit-variant="primary"
    @close="emit('close')"
    @submit="submitFinish"
  >
    <p class="font-body-sm text-on-surface-variant mb-4">
      We've estimated how long these items will stay fresh based on their
      category. Please adjust any dates if necessary before adding them to your
      inventory.
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
</template>
