<?php

use Builditbd\FilamentSettings\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (! function_exists('cz_setting')) {
    function cz_setting($key = null)
    {
        if (Schema::hasTable('settings')) {
            try {
                $allSettings = Cache::rememberForever('all-settings', function () {
                    return Setting::all()->keyBy('key');
                });
                if ($key) {
                    if (isset($allSettings[$key])) {
                        $data = $allSettings[$key];
            
                        if ($data->type == 'image') {
                            return asset($data->value);
                        } else {
                            return $data->value;
                        }
                    }
                }
                return $allSettings;
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
}