<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DebugAging extends Command
{
    protected $signature = 'debug:aging';
    protected $description = 'Debug stock aging buckets';

    public function handle()
    {
        $user = User::find(5);
        $now = now();
        
        $this->info("=== Stock Aging Debug for User {$user->id} ===");
        
        // Get aging data
        $allAges = Supply::forUser($user)
            ->select(DB::raw('DATEDIFF(?, COALESCE(last_updated, updated_at)) as days_diff'))
            ->addBinding($now->toDateString())
            ->get()
            ->pluck('days_diff');

        $buckets = [0, 0, 0, 0];
        foreach ($allAges as $d) {
            $d = (int) $d;
            if ($d <= 30) $buckets[0]++;
            elseif ($d <= 60) $buckets[1]++;
            elseif ($d <= 90) $buckets[2]++;
            else $buckets[3]++;
        }

        $this->info("Aging buckets:");
        $this->info("≤30d: {$buckets[0]}");
        $this->info("31-60d: {$buckets[1]}");
        $this->info("61-90d: {$buckets[2]}");
        $this->info(">90d: {$buckets[3]}");
        $this->info("");
        
        $this->info("Sample days_diff values: " . $allAges->take(10)->implode(', '));
        $this->info("Total supplies: " . $allAges->count());
        
        // Show some actual supplies with their dates
        $supplies = Supply::forUser($user)
            ->select('description', 'updated_at', 'last_updated', 
                DB::raw('DATEDIFF(?, COALESCE(last_updated, updated_at)) as days_diff'))
            ->addBinding($now->toDateString())
            ->limit(5)
            ->get();
            
        $this->info("");
        $this->info("Sample supplies with dates:");
        foreach ($supplies as $supply) {
            $this->info("- {$supply->description}: {$supply->days_diff} days old (last_updated: {$supply->last_updated}, updated_at: {$supply->updated_at})");
        }
    }
}