<?php

namespace DiscoveryDesign\FilamentGaze\Forms\Components;

use Carbon\Carbon;
use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;

/**
 * Class GazeBanner
 *
 * Represents a custom form component called GazeBanner.
 * This component displays a banner with the names of the current viewers.
 * It provides methods to set a custom identifier and the poll timer.
 * The component refreshes the list of viewers and renders the banner.
 */
class GazeBanner extends Component
{
    /**
     * The array of current viewers.
     */
    public array $currentViewers = [];

    /**
     * The custom identifier for the GazeBanner component.
     */
    public ?string $identifier = null;

    /**
     * The poll timer for refreshing the list of viewers.
     */
    public string | int $pollTimer = 10;

    /**
     * Whether the lockable trait has been enabled.
     */
    public bool $isLockable = false;

    /**
     * Whether the lockable trait has been enabled.
     */
    public bool $canTakeControl = false;

    public $takeControlButton = null;

    /**
     * Create a new instance of the GazeBanner component.
     */
    public static function make(): static
    {
        $static = app(static::class);
        $static->configure();

        $static->takeControlButton = Action::make('takeControl')
            ->label(__('filament-gaze::gaze.lock_take_control') . ' 123')
            ->color('primary')
            ->action(function (GazeBanner $component) use ($static): void {
                $static->takeControl();
            });

        return $static;
    }

    /**
     * Configure the GazeBanner component.
     *
     * This method sets the component's view and initializes the current viewers.
     */
    /**
     * Set a custom identifier for the GazeBanner component.
     *
     * @param  string  $identifier
     * @return $this
     */
    public function identifier(string | Closure $fnc = ''): static
    {
        $this->identifier = (string) $this->evaluate($fnc);

        return $this;
    }

    /**
     * Set the poll timer for refreshing the list of viewers.
     *
     * @param  int  $poll
     * @return $this
     */
    public function pollTimer($poll)
    {
        $this->pollTimer = $poll;
        $this->poll($this->pollTimer);

        return $this;
    }

    /**
     * Set the lock state
     */
    public function lock(bool | Closure $fnc = true): static
    {
        $this->isLockable = (bool) $this->evaluate($fnc);

        if ($this->isLockable) {
            // Only attempt to interact with Livewire once the container is initialized.
            if (isset($this->container)) {
                $this->refreshForm();
                $this->takeControl();
            }
        }

        return $this;
    }

    /*
     *  Helper function to hide on create
     */

    public function hideOnCreate(): static
    {
        $this->hidden(fn ($record) => $record === null);

        return $this;
    }

    /**
     * Set the take control state
     */
    public function canTakeControl(bool | Closure $fnc = true): static
    {
        $this->canTakeControl = (bool) $this->evaluate($fnc);

        return $this;
    }

    public function takeControl()
    {
        dd(1);

        if (!isset($this->container)) {
            return;
        }

        // Set everyone but self to false
        $identifier = $this->getIdentifier();
        $curViewers = Cache::get('filament-gaze-' . $identifier, []);

        $authGuard = Filament::getCurrentPanel()->getAuthGuard();
        foreach ($curViewers as $key => $viewer) {
            if ($viewer['id'] == auth()->guard($authGuard)->id()) {
                $curViewers[$key]['has_control'] = true;
            } else {
                $curViewers[$key]['has_control'] = false;
            }
        }

        Cache::put('filament-gaze-' . $identifier, $curViewers, now()->addSeconds(max([5, $this->pollTimer * 2])));

    }

    public function getIdentifier()
    {
        if (! $this->identifier) {
            $record = $this->getRecord();
            if (! $record) {
                $this->identifier = (string) $this->getModel();
            } else {
                $this->identifier = get_class($record) . '-' . $record->id;
            }
        }

        return $this->identifier;
    }

    public function refreshForm()
    {
        // Avoid accessing the container before Filament initializes it.
        if (! isset($this->container)) {
            return;
        }

        // Very hacky, maybe a better solution for this?
        $record = $this->getRecord();

        if ($record) {
            $this->getLivewire()->mount($record->{$record->getRouteKeyName()});
        }
    }

    /**
     * Refresh the list of viewers.
     *
     * If no custom identifier is set, it will use the model's identifier.
     * It retrieves the current viewers, removes expired viewers, and adds/re-adds the current user.
     *
     * @return void
     */
    public function refreshViewers()
    {
        if (! isset($this->container)) {
            return;
        }

        $identifier = $this->getIdentifier();
        $authGuard = Filament::getCurrentPanel()->getAuthGuard();

        $someoneHasLockState = false;
        $lockState = false;

        // Check over all current viewers
        $curViewers = Cache::get('filament-gaze-' . $identifier, []);
        foreach ($curViewers as $key => $viewer) {

            if (isset($viewer['guard']) && $authGuard !== $viewer['guard']) {
                unset($curViewers[$key]);

                continue;
            }

            // Remove expired viewers
            if (Carbon::parse($viewer['expires'])->isPast()) {
                unset($curViewers[$key]);

                continue;
            }

            // If current user, remove them so they can be re-added below.
            if ($viewer['id'] == auth()->guard($authGuard)?->id()) {
                // Preserve their active lock state
                $lockState = $viewer['has_control'];

                unset($curViewers[$key]);
            }

            if ($viewer['has_control']) {
                $someoneHasLockState = true;
            }
        }

        $user = auth()->guard($authGuard)->user();
        // Add/re-add the current user to the list
        $curViewers[] = [
            'id' => auth()->guard($authGuard)->id(),
            'guard' => $authGuard,
            'name' => $user?->name ?? $user?->getFilamentName() ?? 'Unknown', // Possibly need to account for more?
            'expires' => now()->addSeconds(max([5, $this->pollTimer * 2])),
            'has_control' => $this->isLockable && ($lockState || (count($curViewers) === 0)),
        ];

        // If no one has lock state, give it to the first person.
        // Annoyingly the table isn't sorted so we can't just grab the first person.
        if ($this->isLockable && ! $someoneHasLockState) {
            foreach ($curViewers as $key => $viewer) {
                $curViewers[$key]['has_control'] = true;

                // Refresh the form is it's the current user being given control.
                if ($viewer['id'] == auth()->guard($authGuard)->id()) {
                    $this->refreshForm();
                }

                break;
            }
        }

        $this->currentViewers = $curViewers;

        Cache::put('filament-gaze-' . $identifier, $curViewers, now()->addSeconds($this->pollTimer * 2));
    }

    /**
     * Render the GazeBanner component.
     *
     * It refreshes the list of viewers, formats the viewer names, and returns the rendered view.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        $this->refreshViewers();

        $formattedViewers = '';
        $currentViewers = collect($this->currentViewers);
        $authGuard = Filament::getCurrentPanel()->getAuthGuard();
        $filteredViewers = $currentViewers->filter(function ($viewer) use ($authGuard) {
            return $viewer['id'] != auth()->guard($authGuard)->id();
        });

        $finalText = '';

        if ($filteredViewers->count() > 2) {
            $formattedViewers = $filteredViewers->first()['name'];
            $formattedViewers .= ', ';
            $formattedViewers .= $filteredViewers->skip(1)->first()['name'];

            $extras = $filteredViewers->count() - 2;

            $finalText = __($extras > 1 ? 'filament-gaze::gaze.banner_text_others' : 'filament-gaze::gaze.banner_text_other', [
                'viewers' => $formattedViewers,
                'count' => $extras,
            ]);

        } else {
            $formattedViewers = $filteredViewers->implode('name', ' & ');

            $finalText = __('filament-gaze::gaze.banner_text', [
                'viewers' => $formattedViewers,
            ]);
        }

        $lockUser = collect($this->currentViewers)->where('has_control', true)->first();
        $hasControl = isset($lockUser) && $lockUser['id'] == auth()->guard($authGuard)->id();

        if ($this->isLockable && isset($this->container)) {
            if($form = $this->getLivewire()->getSchema('form')){
                $form->disabled(! $hasControl);
            }
            if($childForm = $this->getLivewire()->getSchema('mountedTableActionForm')){
                $childForm->disabled(! $hasControl);
            }
        }

        return view('filament-gaze::forms.components.gaze-banner', [
            'show' => $filteredViewers->count() >= 1,
            'currentViewers' => $this->currentViewers,
            'text' => $finalText,
            'pollTimer' => $this->pollTimer,
            'isLockable' => $this->isLockable,
            'controlUser' => $lockUser ?? false,
            'hasControl' => $hasControl,
            'canTakeControl' => $this->canTakeControl,
            'takeControlButton' => $this->takeControlButton,
        ]);
    }
}
