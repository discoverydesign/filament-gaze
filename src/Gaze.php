<?php

declare(strict_types=1);

namespace DiscoveryDesign\FilamentGaze;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class Gaze
{
    public static function isOpened(null|Model $record, bool $excludeCurrentUser = true): bool
    {
        $viewers = self::getViewers($record, $excludeCurrentUser);

        return count($viewers) > 0;
    }

    public static function getViewers(null|Model $record, bool $excludeCurrentUser = true): array
    {
        if (!$record) {
            return [];
        }

        $currentUserId = self::getCurrentUserId();
        $viewers = [];

        foreach (self::getRawViewers($record) as $viewer) {
            if ($excludeCurrentUser && isset($viewer['id']) && $viewer['id'] === $currentUserId) {
                continue;
            }

            if (isset($viewer['expires']) && now()->lt(Carbon::parse($viewer['expires']))) {
                $viewers[] = $viewer;
            }
        }

        return $viewers;
    }

    private static function getCurrentUserId(): mixed
    {
        $authGuard = Filament::getCurrentPanel()?->getAuthGuard() ?? config('filament.default_auth_guard', 'web');

        return auth()->guard($authGuard)?->id();
    }

    private static function getRawViewers(Model $record): array
    {
        $identifier = self::getIdentifier($record);

        return Cache::get('filament-gaze-' . $identifier, []);
    }

    public static function getIdentifier(Model $record): string
    {
        return get_class($record) . '-' . $record->getKey();
    }

    public static function getViewerCount(null|Model $record, bool $excludeCurrentUser = false): int
    {
        $viewers = self::getViewers($record, $excludeCurrentUser);

        return count($viewers);
    }

    public static function isLockedByOther(null|Model $record): bool
    {
        if (!$record) {
            return false;
        }

        $currentUserId = self::getCurrentUserId();

        foreach (self::getRawViewers($record) as $viewer) {
            if (
                ($viewer['has_control'] ?? false) === true
                && isset($viewer['id']) && $viewer['id'] !== $currentUserId
                && isset($viewer['expires']) && now()->lt(Carbon::parse($viewer['expires']))
            ) {
                return true;
            }
        }

        return false;
    }
}
