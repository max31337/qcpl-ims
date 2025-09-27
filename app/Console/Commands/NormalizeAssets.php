<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class NormalizeAssets extends Command
{
    protected $signature = 'assets:normalize {--dry-run : Show what would change without writing}';
    protected $description = 'Split any asset rows with quantity > 1 into individual per-item assets with unique property numbers.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $query = Asset::where('quantity', '>', 1)->orderBy('id');
        $count = $query->count();
        if ($count === 0) {
            $this->info('No assets with quantity > 1 found.');
            return self::SUCCESS;
        }

        $this->warn("Found {$count} asset(s) with quantity > 1.");

        if ($dry) {
            foreach ($query->get() as $asset) {
                $this->line("- Asset #{$asset->id} {$asset->property_number} will be split into {$asset->quantity} items");
            }
            return self::SUCCESS;
        }

        DB::transaction(function () use ($query) {
            foreach ($query->lockForUpdate()->get() as $asset) {
                $qty = (int) $asset->quantity;
                // For each additional item, create a new asset row
                for ($i = 0; $i < $qty; $i++) {
                    $data = $asset->replicate(['property_number','quantity','total_cost','created_at','updated_at'])->toArray();
                    $data['property_number'] = Asset::generatePropertyNumber();
                    $data['quantity'] = 1;
                    $data['total_cost'] = $asset->unit_cost; // per-item
                    Asset::create($data);
                }
                // Delete original aggregated row
                $asset->delete();
            }
        });

        $this->info('Normalization complete: assets are now per-item with unique property numbers.');
        return self::SUCCESS;
    }
}
