<div>
    <div
        x-data="{}"
        wire:poll.60s="refreshViewers"
    >
        @if($show)
            <div class="py-8">
                <div class="flex flex-row space-x-4 rounded-xl p-6 text-primary-300 bg-primary-50 border border-primary-300" style="--c-50:var(--primary-50);--c-400:var(--primary-400);--c-600:var(--primary-600);">
                    <div>
                        <x-filament::icon
                            alias="panels::filament-gaze.banner.icon"
                            icon="heroicon-m-eye"
                            class="h-5 w-5"
                        />
                    </div>

                    <p>
                        {{ $text }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
