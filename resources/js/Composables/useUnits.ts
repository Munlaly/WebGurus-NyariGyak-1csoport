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

  const formatQuantity = (amount: any | null, unit: string) => {
    if (amount === null || amount === undefined) return '';

    if (unitSystem.value === 'imperial') {
      if (unit === 'g') {
        const ounces = amount * 0.035274;
        return ounces >= 16
          ? `${(ounces / 16).toFixed(1)} lbs`
          : `${ounces.toFixed(1)} oz`;
      }
      if (unit === 'kg') {
        return `${(amount * 2.20462).toFixed(1)} lbs`;
      }
      if (unit === 'ml') {
        const flOz = amount * 0.033814;
        return flOz >= 8
          ? `${(flOz / 8).toFixed(1)} cups`
          : `${flOz.toFixed(1)} fl oz`;
      }
      if (unit === 'l') {
        return `${(amount * 2.11338).toFixed(1)} cups`;
      }
    }
    return `${amount} ${unit}`;
  };

  const formatInputAmount = (amount: any, unit: string) => {
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
  };

  const fromStorageAmount = (
    storedAmount: number,
    storageUnit: string,
  ): number => {
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
  };

  const toStorageAmount = (
    displayAmount: number,
    storageUnit: string,
  ): number => {
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
  };

  // The short unit label to show next to an editable field for a given
  // storage unit — 'oz' instead of 'g' when the person is in imperial mode.
  const getDisplayUnit = (storageUnit: string): string => {
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
  };

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
