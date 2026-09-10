<script setup lang="ts">
import ActionModal from './ActionModal.vue';
import { getCategoryEmoji } from '../../utils/inventory';
import { useUnits } from '../../Composables/useUnits.js';
import { InventoryItem } from '../../Types/inventoryInterfaces.js';

const { formatQuantity, getDisplayUnit } = useUnits();

interface ModalState {
  isOpen: boolean;
  form: {
    processing: boolean;
  };
  selectedItem: InventoryItem | null;
}

defineProps<{
  modalState: ModalState;
  title: string;
  submitText: string;
  submitVariant: 'primary' | 'error';
  inputLabel: string;
  displayAmount: number;
  itemUnit: string;
}>();

const emit = defineEmits(['update:displayAmount', 'close', 'submit']);
</script>

<template>
  <ActionModal
    :show="modalState.isOpen"
    :title="title"
    :processing="modalState.form.processing"
    :submit-text="submitText"
    :submit-variant="submitVariant"
    @close="emit('close')"
    @submit="emit('submit')"
  >
    <div
      v-if="modalState.selectedItem"
      class="bg-surface-container-lowest border-outline-variant/30 mb-2 flex items-center gap-4 rounded-xl border p-4 shadow-inner"
    >
      <span class="text-4xl">{{
        modalState.selectedItem.ingredient.emoji ||
        getCategoryEmoji(modalState.selectedItem.ingredient.category?.name)
      }}</span>
      <div>
        <span class="font-label-lg text-on-surface block font-bold capitalize">
          {{ modalState.selectedItem.ingredient.name }}
        </span>
        <span class="font-body-sm text-on-surface-variant">
          Current:
          {{
            formatQuantity(
              modalState.selectedItem.amount_left,
              modalState.selectedItem.unit ||
                modalState.selectedItem.ingredient.base_unit ||
                '',
            )
          }}
        </span>
      </div>
    </div>

    <div>
      <label
        class="font-label-sm text-on-surface-variant mb-1.5 block font-medium"
      >
        {{ inputLabel }}
      </label>
      <div class="flex items-center gap-3">
        <input
          :value="displayAmount"
          type="number"
          min="0.1"
          step="0.1"
          class="bg-surface-container-lowest border-outline-variant text-on-surface focus:ring-primary w-full rounded-xl border p-3 font-bold transition-all focus:ring-2"
          required
          @input="
            emit(
              'update:displayAmount',
              Number(($event.target as HTMLInputElement).value),
            )
          "
        />
        <span
          v-if="itemUnit"
          class="text-on-surface-variant text-sm font-medium whitespace-nowrap"
        >
          {{ getDisplayUnit(itemUnit) }}
        </span>
      </div>
    </div>
  </ActionModal>
</template>
