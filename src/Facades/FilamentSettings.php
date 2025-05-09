<?php

namespace Builditbd\FilamentSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Builditbd\FilamentSettings\FilamentSettings
 */
class FilamentSettings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Builditbd\FilamentSettings\FilamentSettings::class;
    }
}
