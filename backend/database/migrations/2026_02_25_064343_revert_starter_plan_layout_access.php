<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Revert Starter plan to premium_layout_access = 0.
     *
     * Final plan layout access matrix:
     *   Free/Starter        → Default (Simple) only
     *   Pro                 → Default + Half Page
     *   Enterprise          → Default + Professional + Grid Premium + Half Page
     *   Enterprise Business → All (+ Classic Grid)
     *
     * The granular per-layout access is now handled in the frontend
     * via plan slug checks, not just the boolean feature flag.
     */
    public function up(): void
    {
        $starterPlan = DB::table('plans')->where('slug', 'starter')->first();
        $feature = DB::table('features')->where('slug', 'premium_layout_access')->first();

        if ($starterPlan && $feature) {
            DB::table('plan_features')
                ->where('plan_id', $starterPlan->id)
                ->where('feature_id', $feature->id)
                ->update(['limit' => 0]);
        }
    }

    public function down(): void
    {
        $starterPlan = DB::table('plans')->where('slug', 'starter')->first();
        $feature = DB::table('features')->where('slug', 'premium_layout_access')->first();

        if ($starterPlan && $feature) {
            DB::table('plan_features')
                ->where('plan_id', $starterPlan->id)
                ->where('feature_id', $feature->id)
                ->update(['limit' => 1]);
        }
    }
};
