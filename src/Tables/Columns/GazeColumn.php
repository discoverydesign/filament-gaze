<?php

declare(strict_types=1);

namespace DiscoveryDesign\FilamentGaze\Tables\Columns;

use DiscoveryDesign\FilamentGaze\Gaze;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;

final class GazeColumn extends TextColumn
{
    protected bool $excludeCurrentUser = true;

    public static function make(string|null $name = null): static
    {
        return parent::make($name ?? 'gaze_status');
    }

    public function excludeCurrentUser(bool $exclude = true): static
    {
        $this->excludeCurrentUser = $exclude;

        return $this;
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
            ->icon(Heroicon::OutlinedUser)
            ->iconColor(fn ($record) => $this->getViewerCount($record) > 0 ? 'danger' : 'success')
            ->formatStateUsing(fn ($record) => $this->getViewerCount($record))
            ->getStateUsing(fn ($record): int => Gaze::getViewerCount($record, $this->excludeCurrentUser))
            ->iconColor(fn ($state): string => $state > 0 ? 'danger' : 'success')
            ->toggleable();
    }
}
