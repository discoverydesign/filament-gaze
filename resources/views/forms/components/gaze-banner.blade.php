@php
    use Filament\Support\Icons\Heroicon;
    use Filament\Support\Enums\IconSize;
@endphp

<div
    class="gaze-banner @if($show) gaze-banner--has-content @endif"
    x-data="{}"
    x-on:filament-gaze-take-control.window="
        if ($event.detail.componentId === '{{ $getId() }}') {
            // Use Livewire's event system to call the method
            $wire.dispatch('filament-gaze-take-control-handler', { componentId: '{{ $getId() }}' })
        }
    "
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
                <x-filament::button 
                    class="button-container" 
                    color="primary" 
                    x-on:click="
                        $dispatch('filament-gaze-take-control', { componentId: '{{ $getId() }}' })
                    "
                >
                    {{ __('filament-gaze::gaze.lock_take_control') }}
                </x-filament::button>
            @endif
        </div>
    @endif
</div>
