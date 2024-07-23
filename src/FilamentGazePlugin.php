<?php

namespace DiscoveryDesign\FilamentGaze;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

/**
 * Class FilamentGazePlugin
 *
 * This class represents the Filament Gaze plugin.
 * It implements the Plugin interface and provides methods for booting and registering the plugin.
 */
class FilamentGazePlugin implements Plugin
{
    /**
     * Create a new instance of the plugin.
     *
     * @return static
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Get the ID of the plugin.
     *
     * @return string
     */
    public function getId(): string
    {
        return 'filament-gaze';
    }

    /**
     * Boot the plugin.
     *
     * @param Panel $panel The Filament panel instance.
     * @return void
     */
    public function boot(Panel $panel): void {}

    /**
     * Register the plugin.
     *
     * @param Panel $panel The Filament panel instance.
     * @return void
     */
    public function register(Panel $panel): void {}
}
