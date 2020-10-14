<?php
namespace BSProxy;

use Illuminate\Filesystem\Cache;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor(){
        return Proxy::class;
    }
}