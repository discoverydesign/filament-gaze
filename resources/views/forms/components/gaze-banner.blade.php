@php
    use Filament\Support\Icons\Heroicon;
    use Filament\Support\Enums\IconSize;
@endphp

<div
    class="gaze-banner @if($show) gaze-banner--has-content @endif"
    x-data="{}"
    wire:poll.keep-alive.{{ $pollTimer }}s="$dispatch('FilamentGaze::refreshViewers')"
>
    @if($show)
        <div class="fi-gaze-banner">
            <div class="icon-container">
                @if($isLockable && !$hasControl)
                    <x-filament::icon
                        alias="filament-gaze::banner.locked"
                        :icon="Heroicon::LockClosed"
                        :size="IconSize::Large"
                    />
                @else
                    <x-filament::icon
                        alias="filament-gaze::banner.view"
                        :icon="Heroicon::Eye"
                        :size="IconSize::Large"
                    />
                @endif
            </div>

            <div class="text-container">
                <p>{{ $text }}</p>
                @if($isLockable)
                    @if($hasControl)
                        <p>{{ __('filament-gaze::gaze.lock_is_controller') }}</p>
                    @elseif($controlUser)
                        <p>{{ __('filament-gaze::gaze.lock_user_controller', ['name' => $controlUser['name']]) }}</p>
                    @endif
                @endif
            </div>

            @if($isLockable && !$hasControl && $canTakeControl)
                <x-filament::button class="button-container" color="primary" wire:click="$dispatch('FilamentGaze::takeControl')">
                    {{ __('filament-gaze::gaze.lock_take_control') }}
                </x-filament::button>
            @endif
        </div>
    @endif
</div>
