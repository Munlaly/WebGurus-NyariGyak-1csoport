import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface InertiaPageProps {
  auth?: {
    unitSystem?: 'metric' | 'imperial';
    [key: string]: unknown;
  };
  [key: string]: unknown;
}

export interface UnitOption {
  value: string;
  label: string;
}

export function useUnits() {
  const page = usePage();
  const unitSystem = computed(() => {
    const props = page.props as InertiaPageProps;
    return props.auth?.unitSystem || 'metric';
  });

  function formatQuantity(amount: number | string | null, unit: string) {
    if (amount === null || amount === undefined || amount === '') return '';
    const num = Number(amount);
    if (isNaN(num)) {
      return `${amount} ${unit}`;
    }
    const safeUnit = (unit || '').trim().toLowerCase();

    if (unitSystem.value === 'imperial') {
      if (safeUnit === 'g') {
        const ounces = num * 0.035274;
        return ounces >= 16
          ? `${(ounces / 16).toFixed(1)} lbs`
          : `${ounces.toFixed(1)} oz`;
      }
      if (safeUnit === 'kg') {
        return `${(num * 2.20462).toFixed(1)} lbs`;
      }
      if (safeUnit === 'ml') {
        const flOz = num * 0.033814;
        return flOz >= 8
          ? `${(flOz / 8).toFixed(1)} cups`
          : `${flOz.toFixed(1)} fl oz`;
      }
      if (safeUnit === 'l') {
        return `${(num * 2.11338).toFixed(1)} cups`;
      }
    }
    return `${num} ${safeUnit}`;
  }

  function formatInputAmount(amount: number | string, unit: string) {
    const num = Number(amount);
    if (isNaN(num)) return { amount, unit };

    const safeUnit = (unit || '').trim().toLowerCase();

    if (unitSystem.value === 'imperial') {
      if (safeUnit === 'g') {
        return { amount: Number((num * 0.035274).toFixed(1)), unit: 'oz' };
      }
      if (safeUnit === 'ml') {
        return {
          amount: Number((num * 0.033814).toFixed(1)),
          unit: 'fl oz',
        };
      }
    }
    return { amount: num, unit: safeUnit };
  }

  function fromStorageAmount(
    storedAmount: number,
    storageUnit: string,
  ): number {
    if (unitSystem.value !== 'imperial') return storedAmount;
    switch (storageUnit) {
      case 'g':
        return Number((storedAmount * 0.035274).toFixed(2));
      case 'kg':
        return Number((storedAmount * 2.20462).toFixed(2));
      case 'ml':
        return Number((storedAmount * 0.033814).toFixed(2));
      case 'l':
        return Number((storedAmount * 2.11338).toFixed(2));
      default:
        return storedAmount;
    }
  }

  function toStorageAmount(displayAmount: number, storageUnit: string): number {
    if (unitSystem.value !== 'imperial') return displayAmount;
    switch (storageUnit) {
      case 'g':
        return Number((displayAmount / 0.035274).toFixed(2));
      case 'kg':
        return Number((displayAmount / 2.20462).toFixed(3));
      case 'ml':
        return Number((displayAmount / 0.033814).toFixed(2));
      case 'l':
        return Number((displayAmount / 2.11338).toFixed(3));
      default:
        return displayAmount;
    }
  }

  function getDisplayUnit(storageUnit: string): string {
    if (unitSystem.value === 'imperial') {
      const labels: Record<string, string> = {
        g: 'oz',
        kg: 'lbs',
        ml: 'fl oz',
        l: 'cups',
        pcs: 'pcs',
      };
      return labels[storageUnit] ?? storageUnit;
    }
    return storageUnit;
  }

  const unitOptions = computed<UnitOption[]>(() => {
    if (unitSystem.value === 'imperial') {
      return [
        { value: 'pcs', label: 'Pieces (pcs)' },
        { value: 'g', label: 'Ounces (oz)' },
        { value: 'kg', label: 'Pounds (lbs)' },
        { value: 'ml', label: 'Fluid Ounces (fl oz)' },
        { value: 'l', label: 'Cups' },
      ];
    }
    return [
      { value: 'pcs', label: 'Pieces (pcs)' },
      { value: 'g', label: 'Grams (g)' },
      { value: 'kg', label: 'Kilos (kg)' },
      { value: 'ml', label: 'Milliliters (ml)' },
      { value: 'l', label: 'Liters (l)' },
    ];
  });

  return {
    unitSystem,
    formatQuantity,
    formatInputAmount,
    fromStorageAmount,
    toStorageAmount,
    getDisplayUnit,
    unitOptions,
  };
}
