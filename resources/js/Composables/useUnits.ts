import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

interface InertiaPageProps {
  auth?: {
    unitSystem?: 'metric' | 'imperial';
    [key: string]: unknown;
  };
  [key: string]: unknown;
}

export function useUnits() {
  const page = usePage();
  const unitSystem = computed(() => {
    const props = page.props as InertiaPageProps;
    return props.auth?.unitSystem || 'metric';
  });

  const formatQuantity = (amount: number | null, unit: string) => {
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

  const formatInputAmount = (amount: number, unit: string) => {
    if (unitSystem.value === 'imperial') {
      if (unit === 'g') {
        return { amount: Number((amount * 0.035274).toFixed(1)), unit: 'oz' };
      }
      if (unit === 'ml') {
        return {
          amount: Number((amount * 0.033814).toFixed(1)),
          unit: 'fl oz',
        };
      }
    }
    return { amount, unit };
  };

  return {
    unitSystem,
    formatQuantity,
    formatInputAmount,
  };
}
