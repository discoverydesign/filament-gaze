<?php

namespace OwainJones74\FilamentGaze;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use OwainJones74\FilamentGaze\Livewire\FilamentGazeBanner;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentGazeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-gaze')
            ->hasViews();
    }

    public function packageBooted()
    {
        // Register the component with Livewire, so we can use all of its goodness
        Livewire::component('filament-gaze-banner', FilamentGazeBanner::class);
    }
}
