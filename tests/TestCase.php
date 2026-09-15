<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $guard = null)
    {
        if ($guard === null) {
            \Illuminate\Support\Facades\Auth::guard($user instanceof \App\Models\Admin ? 'web' : 'admin')->logout();
        }
        return parent::actingAs($user, $guard ?? ($user instanceof \App\Models\Admin ? 'admin' : 'web'));
    }
}
