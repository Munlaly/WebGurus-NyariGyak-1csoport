<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FindBadUtf8 extends Command
{
    protected $signature = 'find:bad-utf8';
    protected $description = 'Scan recipes, recipe_ingredients, and ingredients for malformed UTF-8 data';

    public function handle(): void
    {
        $tables = [
            'recipes' => DB::table('recipes')->get(),
            'recipe_ingredients' => DB::table('recipe_ingredients')->get(),
            'ingredients' => DB::table('ingredients')->get(),
        ];

        $found = false;

        foreach ($tables as $tableName => $rows) {
            foreach ($rows as $row) {
                foreach ((array) $row as $column => $value) {
                    if (is_string($value) && $value !== '' && !mb_check_encoding($value, 'UTF-8')) {
                        $found = true;
                        $id = $row->id ?? '(no id)';
                        $this->error("TABLE: {$tableName}  ID: {$id}  COLUMN: {$column}");
                        $this->line('  hex: ' . bin2hex($value));
                        $this->line('  cleaned guess: ' . mb_convert_encoding($value, 'UTF-8', 'UTF-8'));
                        $this->newLine();
                    }
                }
            }
        }

        if (!$found) {
            $this->info('No invalid UTF-8 found in recipes / recipe_ingredients / ingredients.');
            $this->info('Check other tables the Recipes page touches (e.g. users.favoriteRecipes pivot, categories).');
        }
    }
}