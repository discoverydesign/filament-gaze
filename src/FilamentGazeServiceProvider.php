<?php

namespace DiscoveryDesign\FilamentGaze;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentGazeServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-gaze';

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-gaze')
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void {}
}
