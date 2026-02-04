@php
    use Filament\Support\Icons\Heroicon;
    use Filament\Support\Enums\IconSize;
@endphp

<div
    class="gaze-banner @if($show) gaze-banner--has-content @endif"
    wire:key="gaze-banner-{{ $key }}-{{ $hasControl ? '1' : '0' }}"
    x-data="{}"
    x-init="
        window._gazeControlState ??= {};
        const key = @js($key);
        const prev = window._gazeControlState[key];
        const current = @js($hasControl);
        
        if (prev !== undefined && prev !== current) {
            setTimeout(() => {
                const wire = $el.closest('[wire\\:id]');
                if (wire) {
                    const id = wire.getAttribute('wire:id');
                    if (id && window.Livewire) {
                        window.Livewire.find(id)?.callSchemaComponentMethod(key, 'refreshOnControlChange');
                    }
                }
            }, 50);
        }
        
        window._gazeControlState[key] = current;
    "
    @if ($pollTimer)
        wire:poll.{{ $pollTimer }}s
    @endif
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
                    wire:click="callSchemaComponentMethod('{{ $key }}', 'takeControl')"
                >
                    {{ __('filament-gaze::gaze.lock_take_control') }}
                </x-filament::button>
            @endif
        </div>
    @endif
</div>
