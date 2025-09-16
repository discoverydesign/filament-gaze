<?php

namespace DiscoveryDesign\FilamentGaze;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Class FilamentGazeServiceProvider
 *
 * @package DiscoveryDesign\FilamentGaze
 */
class FilamentGazeServiceProvider extends PackageServiceProvider
{
    /**
     * The name of the package.
     *
     * @var string
     */
    public static string $name = 'filament-gaze';

    /**
     * Configure the package.
     *
     * @param Package $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-gaze')
            ->hasTranslations()
            ->hasViews();
    }

    /**
     * Perform any actions after the package has booted.
     *
     * @return void
     */
    public function packageBooted(): void {
        FilamentAsset::register([
            Css::make('filament-gaze-stylesheet', __DIR__ . '/../resources/css/filament-gaze.css'),
        ]);
    }
}
