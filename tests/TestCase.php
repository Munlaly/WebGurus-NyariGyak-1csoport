<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
   protected function setUp(): void
    {
        parent::setUp();
        
        // Globally ignore missing Vite manifests during tests
        $this->withoutVite();
    }
}
