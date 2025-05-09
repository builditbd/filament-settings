<?php

namespace Builditbd\FilamentSettings;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Builditbd\FilamentSettings\Commands\FilamentSettingsCommand;

class FilamentSettingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-settings')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament_settings_table')
            ->hasCommand(FilamentSettingsCommand::class);
    }
}
