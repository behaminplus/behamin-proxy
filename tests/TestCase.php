<?php

namespace Behamin\ServiceProxy\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        config(['bsproxy.proxy_base_url' => 'https://debug.behaminplus.ir/']);
    }
}
