<?php

namespace App\Services;

class IngredientService
{
    public function sanitizeName(string $rawName): string 
    {
        $name = strtolower($rawName);
        if (str_contains($name, ':')) {
            $name = explode(':', $name)[1];
        }

        $name = preg_replace('/(?:\*\*|\(|\s-\s).*/', '', $name);

        $noiseWords = [
            'fresh', 'chopped', 'diced', 'sliced', 'optional', 'garnish', 
            'large', 'medium', 'small', 'the following', 'dry', 'raw',
            'pieces', 'strips', 'cubed', 'cubes', 'roughly', 'finely'
        ];
        $pattern = '/\b(' . implode('|', $noiseWords) . ')\b/i';
        $name = preg_replace($pattern, '', $name);

        return trim(preg_replace('/\s+/', ' ', $name));
    }

    public function standardizeUnit(string $rawUnit): string
    {
        $unit = strtolower(trim($rawUnit));

        // weight
        if (in_array($unit, ['g', 'gram', 'grams', 'gr'])) return 'g';
        if (in_array($unit, ['kg', 'kilo', 'kilogram', 'kilograms'])) return 'kg';
        if (in_array($unit, ['oz', 'ounce', 'ounces'])) return 'oz';
        if (in_array($unit, ['lb', 'lbs', 'pound', 'pounds'])) return 'lb';

        // volume
        if (in_array($unit, ['ml', 'milliliter', 'milliliters'])) return 'ml';
        if (in_array($unit, ['l', 'liter', 'liters'])) return 'l';
        if (in_array($unit, ['c', 'cup', 'cups'])) return 'cup';
        if (in_array($unit, ['t', 'tsp', 'teaspoon', 'teaspoons'])) return 'tsp';
        if (in_array($unit, ['tbs', 'tbsp', 'tablespoon', 'tablespoons'])) return 'tbsp';

        // count (fallback for unusual units(ex. "cloves"))
        if (empty($unit) || in_array($unit, ['serving', 'servings', 'piece', 'pieces', 'pcs'])) {
            return 'pcs';
        }
        // if not recognized, return the original
        return $unit;
    }

    public function getBaseMetricUnit(string $rawUnit) : string {
        $standardUnit = $this->standardizeUnit($rawUnit);
        if(in_array($standardUnit, ['g', 'kg', 'oz', 'lb'])) return 'g';
        if(in_array($standardUnit, ['ml', 'l', 'cup', 'tsp', 'tbsp'])) return 'ml';

        return 'pcs';
    }

    public function convertToBaseAmount(float $amount, string $rawUnit): float {
        $standardUnit = $this->standardizeUnit($rawUnit);

        $multipliers = [
            'g' => 1.0,
            'kg' => 1000.0,
            'oz' => 28.3495,
            'lb' => 453.592,

            'ml' => 1.0,
            'l' => 1000.0,
            'cup' => 236.588,
            'tbsp' => 14.7868,
            'tsp' => 4.92892,

            'pcs' => 1.0,
        ];

        $multiplier = $multipliers[$rawUnit] ?? 1.0;

        return round($amount * $multiplier, 2);
    }

    public function formatForDisplay(float $amount, string $baseUnit, string $systemPreference = 'metric') : array {
        if($systemPreference === 'metric' || $baseUnit === 'pcs') {
            return [
                'amount' => round($amount, 2),
                'unit' => $baseUnit,
            ];
        }

        if($baseUnit === 'g') {
            if($amount >= 453.592) {
                return [
                    'amount' => round($amount / 453.592, 2),
                    'unit' => 'lb',
                ];
            }

            return [
                'amount' => round($amount / 28.3495, 2),
                'unit' => 'oz',
            ];
        }

        if($baseUnit === 'ml') {
            if($amount >= 236.588) {
                return [
                    'amount' => round($amount / 236.588, 2),
                    'unit' => 'cup',
                ];
            }
            if($amount >= 14.7868) {
                return [
                    'amount' => round($amount / 14.7868, 2),
                    'unit' => 'tbsp',
                ];
            }
            return [
                'amount' => round($amount / 4.92892, 2),
                'unit' => 'tsp',
            ];
        }

        return [
            'amount' => round($amount, 2),
            'unit' => $baseUnit,
        ];
    }
}