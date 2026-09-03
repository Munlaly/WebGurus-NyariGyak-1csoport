import type { InventoryItem } from '../Types/inventoryInterfaces';

export const getStatusLabel = (status: 'FULL' | 'OPENED' | 'LOW') => {
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

export const getDiffDays = (dateStr: string | null) => {
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

export const getItemState = (item: InventoryItem) => {
  const diffDays = getDiffDays(item.expiration_date);
  const unit = item.unit || item.ingredient?.base_unit || 'pcs';
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
