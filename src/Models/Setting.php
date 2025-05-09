<?php

namespace Builditbd\FilamentSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'display_name',
        'value',
        'details',
        'type',
        'order',
        'settings_group',
        'remarks',
        'is_permanent',
        'created_by',
        'modified_by',
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('all-settings');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('all-settings');
        });
    }

    public function getValueAttribute($value)
    {
        if ($this->type === 'image' && $value) {
            return asset(Storage::url($value));
        }
        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($this->type === 'image' && $value) {
            // Store the image and update the value
            $path = $value->store('settings', 'public');
            $this->attributes['value'] = $path;
        } else {
            $this->attributes['value'] = $value;
        }
    }
}

