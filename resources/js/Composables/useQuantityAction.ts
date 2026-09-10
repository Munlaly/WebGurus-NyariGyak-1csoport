import { ref, computed, watch } from 'vue';
import { useActionModal } from './useActionModal';
import { useUnits } from './useUnits';
import { InventoryItem } from '../Types/inventoryInterfaces';

export function useQuantityAction(
  routeName: string,
  payloadKey: 'amount_to_add' | 'amount_to_remove',
) {
  const { toStorageAmount } = useUnits();

  const modal = useActionModal<InventoryItem, Record<string, number>>(
    (item) => route(routeName, item.id),
    { [payloadKey]: 1 },
    'put',
  );

  const displayAmount = ref(1);

  const itemUnit = computed(() => {
    const item = modal.selectedItem;
    return item ? item.unit || item.ingredient?.base_unit || '' : '';
  });

  watch([displayAmount, itemUnit], ([newAmount, newUnit]) => {
    modal.form[payloadKey] = toStorageAmount(newAmount, newUnit);
  });

  function openModal(item: InventoryItem) {
    displayAmount.value = 1;
    modal.open(item, { [payloadKey]: 1 });
  }

  return {
    modal,
    displayAmount,
    itemUnit,
    openModal,
  };
}
