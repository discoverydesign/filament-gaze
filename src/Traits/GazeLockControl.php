<?php

namespace DiscoveryDesign\FilamentGaze\Traits;


use Illuminate\Support\Facades\Cache;
/**
 * Trait GazeLockControl
 *
 * This Trait can be used by pages etc to control button access, and is used to contain the identifier logic.
 */
trait GazeLockControl {
    /**
     * The custom identifier for the GazeBanner component.
     */
    public ?string $identifier = null;

    public function getIdentifier(): ?string
    {
        // Try to get the custom identifier first, if one has been set.
        if (! $this->identifier) {
            $record = $this->getRecord();
            if (! $record) {
                $this->identifier = (string) $this->getModel();
            } else {
                $this->identifier = get_class($record) . '-' . $record->id;
            }
        }
        // Ensure there's not a custom identifier set.
        $customIdentifier = Cache::get('filament-gaze-' . $this->identifier . '-custom-identfier');
        if($customIdentifier) {
            $this->identifier = $customIdentifier;
        }
        return $this->identifier;
    }

    /**
     * See if current user has gaze control access.
     * @return bool
     */
    public function hasGazeControl(): bool
    {
        if(!isset(request()->user()?->id)){
            return false;
        }
        $currentViewers = Cache::get('filament-gaze-' . $this->getIdentifier());

        if( is_null($currentViewers) ) {
            return true;
        }
        foreach ($currentViewers as $viewer) {
            if($viewer['id'] === request()->user()->id && $viewer['has_control']) {
                return true;
            }
        }

        return false;
    }

}