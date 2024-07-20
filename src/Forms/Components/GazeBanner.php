<?php

namespace OwainJones74\FilamentGaze\Forms\Components;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Illuminate\Support\Facades\Cache;

class GazeBanner extends Component
{
    public array $currentViewers = [];

    final public function __construct()
    {
        $this->refreshViewers();
    }

    public function refreshViewers()
    {
        // Todo: Possibly change this to something model bound?
        // That way it'll work on resources that share a model?
        $identified = request()->getUri();
        $authGuard = Filament::getCurrentPanel()->getAuthGuard();

        // There must be a better way to do this?
        $guardProvider = config('auth.guards.' . $authGuard . '.provider');
        $guardModel = config('auth.providers.' . $guardProvider . '.model');

        // Check over all current viewers
        $curViewers = Cache::get('filament-gaze-' . $identified, []);
        foreach ($curViewers as $key => $viewer) {
            $model = $guardModel::find($viewer['id']);
            $expires = Carbon::parse($viewer['expires']);

            // Remove exipred viewers
            if ($expires->isPast()) {
                unset($curViewers[$key]);
            }

            // If current user, remove them so they can be readded below.
            if (! $model || ($model?->id == auth()?->id())) {
                unset($curViewers[$key]);
            }
        }

        $user = auth()->user();
        // Add/readd the current user to the list
        $curViewers[] = [
            'id' => auth()->id(),
            'name' => $user?->name ?? $user->getFilamentName() ?? 'Unknown',
            'expires' => Carbon::now()->addMinutes(2),
        ];

        // Testing
        /*
        $curViewers[] = [
            'id' => 2,
            'name' => 'User 2',
            'expires' => Carbon::now()->addMinutes(2),
        ];
        */

        $this->currentViewers = $curViewers;

        Cache::put('filament-gaze-' . $identified, $curViewers, now()->addMinutes(2));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $formattedViewers = '';
        $currentViewers = collect($this->currentViewers);
        $filteredViewers = $currentViewers->filter(function ($viewer) {
            return $viewer['id'] != auth()->id();
        });

        $finalText = '';

        if ($filteredViewers->count() > 2) {
            $formattedViewers = $filteredViewers->first()['name'];
            $formattedViewers .= ', ';
            $formattedViewers .= $filteredViewers->skip(1)->first()['name'];

            $extras = $filteredViewers->count() - 2;

            $finalText = __($extras > 1 ? 'filament-gaze::gaze.banner_text_others' : 'filament-gaze::gaze.banner_text_other', [
                'viewers' => $formattedViewers,
                'count' => $extras
            ]);

        } else {
            $formattedViewers = $filteredViewers->implode('name', ' & ');

            $finalText = __('filament-gaze::gaze.banner_text', [
                'viewers' => $formattedViewers
            ]);
        }


        return view('filament-gaze::forms.components.gaze-banner', [
            'show' => $filteredViewers->count() >= 1,
            'currentViewers' => $this->currentViewers,
            'text' => $finalText,
        ]);
    }

    public static function make(array | Closure $schema = []): static
    {
        $static = app(static::class, ['schema' => $schema]);
        $static->configure();

        return $static;
    }
}
