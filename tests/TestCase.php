<?php

namespace Behamin\ServiceProxy\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        config(['proxy.base_url' => 'https://debug.ir']);
    }
}
