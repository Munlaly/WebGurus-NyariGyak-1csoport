<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionModal from '../../Components/Modals/ActionModal.vue';
import { ShoppingListItem } from '../../Types/shoppingListInterfaces.js';
import { useUnits } from '../../Composables/useUnits.js';

const props = defineProps<{
  show: boolean;
  item: ShoppingListItem | null;
}>();

const emit = defineEmits(['close']);

const { unitOptions, fromStorageAmount, toStorageAmount } = useUnits();

const editForm = useForm({
  quantity: 1,
  unit: 'pcs',
});

const displayQuantity = ref(1);

watch(
  () => props.item,
  (newItem) => {
    if (newItem) {
      editForm.unit = newItem.unit;
      displayQuantity.value = fromStorageAmount(newItem.quantity, newItem.unit);
    }
  },
  { immediate: true },
);

watch(
  [displayQuantity, () => editForm.unit],
  ([newDisplayQuantity, newUnit]) => {
    editForm.quantity = toStorageAmount(newDisplayQuantity, newUnit);
  },
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
          v-model="displayQuantity"
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
          <option
            v-for="opt in unitOptions"
            :key="opt.value"
            :value="opt.value"
          >
            {{ opt.label }}
          </option>
        </select>
      </div>
    </div>
  </ActionModal>
</template>
