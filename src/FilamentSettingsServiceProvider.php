<?php

namespace Builditbd\FilamentSettings;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Builditbd\FilamentSettings\Commands\FilamentSettingsCommand;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Builditbd\FilamentSettings\Models\Setting;

class FilamentSettingsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'app-settings';

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


    public function get(string $key, $default = null)
    {
        $cachedSettings = Cache::rememberForever('all-setting', function () {
            return Setting::all()->keyBy('key');
        });

        if (isset($cachedSettings[$key])) {
            $setting = $cachedSettings[$key];
            //  Add logic to handle different setting types (e.g., images)
             if ($setting->type === 'image') {
                 return asset(Storage::url($setting->value));
             }
            return $setting->value;
        }

        return $default;
    }

    public function getGroup(string $groupName)
    {
         $cachedSettings = Cache::rememberForever('all-setting', function () {
            return Setting::all()->keyBy('key');
        });

        $groupSettings = [];
        foreach($cachedSettings as $key => $setting){
            if($setting->settings_group === $groupName){
                $groupSettings[$key] = $setting->value;
            }
        }
        return $groupSettings;
    }


    public function set(string $key, $value): void
    {
        $setting = Setting::firstOrCreate(['key' => $key]);
        $setting->value = $value;
        $setting->save();

        Cache::forget('all-setting'); // Clear the cache
    }

    public function boot(): FilamentSettingsServiceProvider
    {
        require_once __DIR__.'/Helpers/settings-helper.php';

        return parent::boot();
    }
}
