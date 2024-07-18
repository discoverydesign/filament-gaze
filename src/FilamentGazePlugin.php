<?php

namespace OwainJones74\FilamentGaze;


use Filament\Contracts\Plugin;
use Filament\Panel;
use OwainJones74\FilamentGaze\Http\Middleware\RenderHook;

class FilamentGazePlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'gaze';
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function register(Panel $panel): void
    {

        $panel->middleware([
            RenderHook::class,
        ]);
    }
}
