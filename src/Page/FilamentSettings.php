<?php

/*
 * Copyright CWSPS154. All rights reserved.
 * @auth CWSPS154
 * @link  https://github.com/CWSPS154
 */

namespace Builditbd\FilamentSettings\Page;

use Builditbd\FilamentSettings\FilamentSettingsServiceProvider;
use Builditbd\FilamentSettings\Settings\Forms\AppForm;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class FilamentSettings extends Page
{
    protected static string $view = 'filament-settings::filament.pages.filament-settings';

    public ?array $settings = [];

    public function mount(): void
    {
        $settings = cz_setting();
        $this->form->fill($settings);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Save')
                ->label(__('filament-settings::filament-settings.save'))
                ->color('primary')
                ->submit('save'),
        ];
    }

    public static function getTabs(): array
    {
        $tabs = [];
        $classes = self::getClassesInNamespace('Filament\\Settings\\Forms');
        $sortableClasses = [];

        if (method_exists(AppForm::class, 'getTab') &&
            method_exists(AppForm::class, 'getSortOrder')) {
            $sortableClasses[] = AppForm::class;
        }

        foreach ($classes as $class) {
            if (method_exists($class, 'getTab') && method_exists($class, 'getSortOrder')) {
                $sortableClasses[] = $class;
            }
        }

        usort($sortableClasses, function ($a, $b) {
            return $a::getSortOrder() <=> $b::getSortOrder();
        });

        foreach ($sortableClasses as $class) {
            $tabs[] = $class::getTab();
        }

        return $tabs;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs(self::getTabs())
                    ->persistTabInQueryString(),
            ])
            ->statePath('settings');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($data as $tab => $values) {
            $this->processValues($tab, $values);
        }
        $this->successNotification(__('filament-settings::filament-settings.save-success'));
        redirect(request()->header('Referer'));
    }

    private function processValues($tab, $values, $prefix = ''): void
    {
        if (is_array($values)) {
            foreach ($values as $field => $value) {
                $key = $prefix ? "{$prefix}.{$field}" : $field;

                if (is_array($value)) {
                    if (array_keys($value) === range(0, count($value) - 1)) {
                        foreach ($value as $index => $subValue) {
                            $this->processValues($tab, $subValue, "{$key}.{$index}");
                        }
                    } else {
                        $this->processValues($tab, $value, $key);
                    }
                } else {
                    \Builditbd\FilamentSettings\Models\Setting::updateOrCreate(
                        ['tab' => $tab, 'key' => $key],
                        ['value' => $value]
                    );
                    $cacheKey = 'settings_data.'.$tab.'.'.$key;
                    Cache::forget($cacheKey);
                    Cache::forget('settings_data.all');
                }
            }
        }
    }

    private function successNotification(string $title): void
    {
        Notification::make()
            ->title($title)
            ->success()
            ->send();
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-settings::filament-settings.app.settings');
    }

    public function getTitle(): string|Htmlable
    {
        return __('filament-settings::filament-settings.app.settings');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-cog-8-tooth';
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-settings::filament-settings.system');
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public static function canAccess(): bool
    {
        $plugin = Filament::getCurrentPanel()?->getPlugin(FilamentSettingsServiceProvider::$name);
        $access = $plugin->getCanAccess();
        if (! empty($access) && is_array($access) && isset($access['ability'], $access['arguments'])) {
            return Gate::allows($access['ability'], $access['arguments']);
        }

        return $access;
    }

    protected static function getClassesInNamespace(string $namespace): array
    {
        $composerClassMap = require base_path('vendor/composer/autoload_classmap.php');
        $classes = [];
        foreach ($composerClassMap as $class => $path) {
            if (str_contains($class, $namespace)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
