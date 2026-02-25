<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * All 4 paid plans (Starter, Pro, Enterprise, Enterprise Business)
     * should have premium_layout_access = 1.
     * Only Free tier stays locked to Default layout.
     */
    public function up(): void
    {
        // Find the Starter plan and premium_layout_access feature
        $starterPlan = DB::table('plans')->where('slug', 'starter')->first();
        $feature = DB::table('features')->where('slug', 'premium_layout_access')->first();

        if ($starterPlan && $feature) {
            DB::table('plan_features')
                ->where('plan_id', $starterPlan->id)
                ->where('feature_id', $feature->id)
                ->update(['limit' => 1]);
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
                ->update(['limit' => 0]);
        }
    }
};
