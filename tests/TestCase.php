<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // View memakai @vite(), tapi manifest build tidak ada saat testing.
        $this->withoutVite();
    }
}

