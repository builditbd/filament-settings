<?php

/*
 * Copyright CWSPS154. All rights reserved.
 * @auth CWSPS154
 * @link  https://github.com/CWSPS154
 */

declare(strict_types=1);

namespace Builditbd\FilamentSettings;

use Builditbd\FilamentSettings\FilamentSettingsServiceProvider;
use Closure;
use Builditbd\FilamentSettings\Page\FilamentSettings;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;

class FilamentSettingsPlugin implements Plugin
{
    use EvaluatesClosures;

    /**
     * @var bool|Closure|mixed
     */
    protected bool|array $canAccess = true;

    protected bool|array $canAccessAppSectionTab = true;

    public static array $appAdditionalFields = [];

    public function getId(): string
    {
        return FilamentSettingsServiceProvider::$name;
    }

    public function register(Panel $panel): void
    {
        $logo = cz_setting('site-logo');
        $darkLogo = cz_setting('site-logo-dark');
        $favicon = cz_setting('favicon');
        $panel->pages([
            config('filament-settings.settings-page', FilamentSettings::class),
        ])->brandName(cz_setting('site-name') ?? config('app_name'))
            ->darkModeBrandLogo($darkLogo ? asset('storage/'.$darkLogo) : null)
            ->brandLogo($logo ? asset('storage/'.$logo) : null)
            ->favicon($favicon ? asset('storage/'.$favicon) : null);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public static function make(): static
    {
        return app(static::class);
    }

    private function setAccess(bool|Closure|string $ability, $arguments, &$property): void
    {
        if ($ability instanceof Closure) {
            $property = $this->evaluate($ability);
        } elseif (is_string($ability) && ! is_null($arguments)) {
            $property = [
                'ability' => $ability,
                'arguments' => $arguments,
            ];
        } else {
            $property = (bool) $ability;
        }
    }

    public function canAccess(bool|Closure|string $ability = true, $arguments = null): static
    {
        $this->setAccess($ability, $arguments, $this->canAccess);

        return $this;
    }

    public function getCanAccess(): array|bool
    {
        return $this->canAccess;
    }

    public function canAccessAppSectionTab(bool|Closure|string $ability = true, $arguments = null): static
    {
        $this->setAccess($ability, $arguments, $this->canAccessAppSectionTab);

        return $this;
    }

    public function getCanAccessAppSectionTab(): array|bool
    {
        return $this->canAccessAppSectionTab;
    }

    public function appAdditionalField(array $additionalFields): static
    {
        self::$appAdditionalFields = $additionalFields;

        return $this;
    }
}
