<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Blade;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Manually register Flux component paths for tests
        // This ensures Flux components are available during testing
        if (file_exists(resource_path('views/flux'))) {
            Blade::anonymousComponentPath(resource_path('views/flux'), 'flux');
        }
        
        // Register vendor Flux components
        $vendorFluxPath = base_path('vendor/livewire/flux/stubs/resources/views/flux');
        if (file_exists($vendorFluxPath)) {
            Blade::anonymousComponentPath($vendorFluxPath, 'flux');
        }
    }
}
