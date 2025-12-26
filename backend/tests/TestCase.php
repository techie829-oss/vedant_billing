<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createBusinessUser($overrides = [])
    {
        $user = \App\Models\User::factory()->create();
        $business = \App\Models\Business::factory()->create();

        $user->businesses()->attach($business->id, ['role' => 'owner', 'is_active' => true]);

        // Switch to this business context
        $this->actingAs($user);
        session(['business_id' => $business->id]);

        return [$user, $business];
    }
}
