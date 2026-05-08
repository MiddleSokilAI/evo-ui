<?php

namespace EvoUI\Facades;

use Illuminate\Support\Facades\Facade;

class EvoUI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'EvoUI';
    }
}
