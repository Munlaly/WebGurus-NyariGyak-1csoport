<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixBadUtf8 extends Command
{
    protected $signature = 'fix:bad-utf8 {--apply : Actually write the changes. Without this flag, it only previews.}';
    protected $description = 'Strip invalid UTF-8 bytes from ingredient names and flag junk rows for manual review';

    public function handle(): void
    {
        $apply = $this->option('apply');
        $rows = DB::table('ingredients')->get();

        foreach ($rows as $row) {
            $name = $row->name;
            if (!is_string($name) || $name === '' || mb_check_encoding($name, 'UTF-8')) {
                continue;
            }

            // Strip any byte sequences that aren't valid UTF-8, then trim
            // leftover leading punctuation/whitespace the strip may expose.
            $cleaned = iconv('UTF-8', 'UTF-8//IGNORE', $name);
            $cleaned = ltrim($cleaned, " \t\n\r\0\x0B'\"-–—");

            $usageCount = DB::table('recipe_ingredients')
                ->where('ingredient_id', $row->id)
                ->count();

            // Flag rows that become empty or nearly meaningless after cleaning
            // (e.g. the "———————" divider) instead of silently "fixing" them.
            if (mb_strlen($cleaned) < 2 || !preg_match('/[a-zA-Z]/', $cleaned)) {
                $this->warn("ID {$row->id}: cleaned result looks like junk, not a real ingredient name.");
                $this->line("  original name (hex): " . bin2hex($name));
                $this->line("  cleaned result: '{$cleaned}'");
                $this->line("  used in {$usageCount} recipe_ingredients row(s)");
                $this->line($usageCount === 0
                    ? "  -> Looks safe to DELETE. Not doing it automatically; review and delete manually."
                    : "  -> Referenced by recipes! Do NOT delete without checking those recipes first.");
                $this->newLine();
                continue;
            }

            $this->info("ID {$row->id}: '{$name}' (invalid) -> '{$cleaned}'");

            if ($apply) {
                DB::table('ingredients')->where('id', $row->id)->update(['name' => $cleaned]);
            }
        }

        if (!$apply) {
            $this->newLine();
            $this->comment('Dry run only — nothing was changed. Re-run with --apply to write these fixes.');
        }
    }
}