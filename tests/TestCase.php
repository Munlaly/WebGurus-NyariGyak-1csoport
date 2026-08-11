<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
   protected function setUp(): void
    {
        parent::setUp();
        
        // Hard-bind the Vite mock before the HTTP kernel handles the request
        $this->withoutVite();
    }
}
