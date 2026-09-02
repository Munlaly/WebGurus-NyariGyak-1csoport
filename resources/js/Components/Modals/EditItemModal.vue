<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionModal from '../../Components/Modals/ActionModal.vue';
import { ShoppingListItem } from '../../utils/shopping_list.js';

const props = defineProps<{
  show: boolean;
  item: ShoppingListItem | null;
}>();

const emit = defineEmits(['close']);

const editForm = useForm({
  quantity: 1,
  unit: 'pcs',
});

watch(
  () => props.item,
  (newItem) => {
    if (newItem) {
      editForm.quantity = newItem.quantity;
      editForm.unit = newItem.unit;
    }
  },
  { immediate: true },
);

function submitEdit() {
  if (!props.item) return;

  editForm.put(route('shopping-list.update', props.item.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
      editForm.reset();
    },
  });
}
</script>

<template>
  <ActionModal
    :show="show"
    :title="'Edit ' + (item?.ingredient?.name || 'Item')"
    :processing="editForm.processing"
    submit-text="Save Changes"
    submit-variant="primary"
    @close="emit('close')"
    @submit="submitEdit"
  >
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label
          class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
          >Quantity</label
        >
        <input
          v-model="editForm.quantity"
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
          v-model="editForm.unit"
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
