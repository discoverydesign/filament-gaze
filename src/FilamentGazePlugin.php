<?php

namespace DiscoveryDesign\FilamentGaze;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentGazePlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-gaze';
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function register(Panel $panel): void {}
}
