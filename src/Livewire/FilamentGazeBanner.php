<?php

namespace OwainJones74\FilamentGaze\Livewire;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class FilamentGazeBanner extends Component
{
    public array $currentViewers = [];

    public function mount()
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
        foreach($curViewers as $key => $viewer) {
            $model = $guardModel::find($viewer['id']);
            $expires = Carbon::parse($viewer['expires']);

            // Remove exipred viewers
            if($expires->isPast()) {
                unset($curViewers[$key]);
            }

            // If current user, remove them so they can be readded below.
            if(!$model || ($model?->id == auth()?->id())) {
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
        $curViewers[] = [
            'id' => 2,
            'name' => 'User 2',
            'expires' => Carbon::now()->addMinutes(2),
        ];
        $curViewers[] = [
            'id' => 3,
            'name' => 'User 3',
            'expires' => Carbon::now()->addMinutes(2),
        ];
        $curViewers[] = [
            'id' => 4,
            'name' => 'User 4',
            'expires' => Carbon::now()->addMinutes(2),
        ];
        $curViewers[] = [
            'id' => 5,
            'name' => 'User 5',
            'expires' => Carbon::now()->addMinutes(2),
        ];

        $this->currentViewers = $curViewers;

        Cache::put('filament-gaze-' . $identified, $curViewers, now()->addMinutes(2));
    }

    public function ping()
    {

    }

    public function render()
    {
        $formattedViewers = '';
        $currentViewers = collect($this->currentViewers);
        $filteredViewers = $currentViewers->filter(function($viewer) {
            return $viewer['id'] != auth()->id();
        });

        if ($filteredViewers->count() > 2) {
            $formattedViewers = $filteredViewers->first()['name'];
            $formattedViewers .= ', ';
            $formattedViewers .= $filteredViewers->skip(1)->first()['name'];
            $formattedViewers .= ' and ';
            $formattedViewers .= $filteredViewers->count() - 2 . ' others';
        } else {
            $formattedViewers = $filteredViewers->implode('name', ' & ');
        }

        return view('filament-gaze::livewire.filament-gaze-banner', [
            'show' => $filteredViewers->count() >= 1,
            'currentViewers' => $this->currentViewers,
            'text' => 'This page is currently being viewed by ' . $formattedViewers . '.'
        ]);
    }
}
