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

    /**
     * Standardizes wildly different API units into a few base categories.
     */
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
}