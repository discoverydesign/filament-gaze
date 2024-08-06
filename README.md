# Filament Gaze

👀 See who's viewing a resource in Filament PHP 🔭

![Preview Image](https://raw.githubusercontent.com/discoverydesign/filament-gaze/main/media/1.jpg)

![Packagist Version](https://img.shields.io/packagist/v/discoverydesign/filament-gaze.svg)
![Total Downloads](https://img.shields.io/packagist/dt/discoverydesign/filament-gaze.svg)

This package allows you to display when other users are viewing the same resource in Filament PHP.

https://packagist.org/packages/discoverydesign/filament-gaze

## How to use
1. Install the package using `composer require discoverydesign/filament-gaze`
2. Add `\DiscoveryDesign\FilamentGaze\FilamentGazePlugin::make()` to your Filament Panel provider. 
```
$panel->plugins([
    \DiscoveryDesign\FilamentGaze\FilamentGazePlugin::make()
])
```
3. Publish the assets with `php artisan filament:assets`.
4. Import the package inside your Filament Resource with `use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner`.
5. Add the `GazeBanner` form component to your form with `GazeBanner::make()`.
6. If required, publish the translation files with `php artisan vendor:publish --tag=filament-gaze-translations`.

## Examples

### Basic Example
```php
<?php

namespace App\Filament\Resources;

use DiscoveryDesign\FilamentGaze\Forms\Components\GazeBanner;
// ...

class OrderResource extends Resource
{
    // ...

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                GazeBanner::make(),
                    
                // ...
            ]);
    }
    
    // ...
}
```

### Poll Timer Example
```php
GazeBanner::make()
    ->pollTimer(10),
```

### Identifier Example
```php
GazeBanner::make()
    ->identifier('any-order'),
```

### Lock Example
```php
GazeBanner::make()
    ->lock()
```

### Lock With Control Example
```php
GazeBanner::make()
    ->lock()
    ->canTakeControl(fn() => auth()->user()->isAdmin())
```

### Hiding Gaze Banner on Create Form Example
```php
GazeBanner::make()
    ->hidden(fn (?Order $record) => $record === null),
```


## Docs

### `->pollTimer($timeInSecs)`

#### Description
`polltimer` can be used to set how often the browser should inform Filament that the use is still viewing the page, along with getting an updated list of other users also viewing. It is advised not to put this value too short, as it can cause rate limiting issues. But also not to have this value too long, as it will result in a delayed update of new users viewing the page.

#### Arguments
`timeInSeconds` - (int) The amount of time in seconds between each poll. Default is 30 seconds.

### `->identifier($name)`

#### Description
`identifier` is used as a unique identifier for this gaze banner. Any other gaze banners with the same identifier will share the same list of active users. This can be useful if you want 2 or more difference resources to share the same list of active viewing users.

#### Arguments
`name` - (string) The name of the identifier. Default is the resource's model class combines with model Id.

### `->lock($state)`

#### Description
`lock` can be used to lock the resource for anyone but the current person editing the form. This can be useful if you want to prevent multiple people from editing the same resource at the same time. The controller is the first person to access the resource, or the person who has taken control of the resource.

#### Arguments
`state` - (optional, bool) If the resource is lockable or not.

### `->canTakeControl($fnc)`

#### Description
`canTakeControl` can be used to allow the user to take control of the resource if it is locked by someone else. This can be useful if you want to allow the user to take control of the resource.

#### Arguments
`fnc` - (optional, closure | bool) If the user can take control of the resource. Default is true. If a closure is passed, it should return a bool.


## Author

🚀 [Discovery Design](https://discoverydesign.co.uk)

