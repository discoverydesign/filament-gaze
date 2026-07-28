<?php

declare(strict_types=1);

namespace DiscoveryDesign\FilamentGaze\Tables\Columns;

use Closure;
use DiscoveryDesign\FilamentGaze\Gaze;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

final class GazeColumn extends TextColumn
{
    protected bool $excludeCurrentUser = true;

    protected string | Closure | null $identifier = null;

    public static function make(string|null $name = null): static
    {
        return parent::make($name ?? 'gaze_status');
    }

    public function excludeCurrentUser(bool $exclude = true): static
    {
        $this->excludeCurrentUser = $exclude;

        return $this;
    }

    /**
     * Set a custom identifier, shared with any GazeBanner using the same one.
     *
     * Closures are evaluated per row and receive the record.
     */
    public function identifier(string | Closure | null $fnc = null): static
    {
        $this->identifier = $fnc;

        return $this;
    }

    /**
     * The identifier for a row, falling back to the record itself.
     */
    public function getIdentifier(?Model $record = null): Model | string | null
    {
        $identifier = $this->evaluate($this->identifier, [
            'record' => $record,
        ]);

        return filled($identifier) ? $identifier : $record;
    }

    public function getId(): string
    {
        return 'filament-gaze-gaze-column';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('filament-gaze::gaze.column_label'))
            ->tooltip(__('filament-gaze::gaze.tooltip'))
            ->icon(Heroicon::OutlinedEye)
            ->getStateUsing(fn ($record): int => Gaze::getViewerCount($this->getIdentifier($record), $this->excludeCurrentUser))
            ->iconColor(fn ($state): string => $state > 0 ? 'primary' : 'gray')
            ->toggleable();
    }
}
