<?php

declare(strict_types=1);

namespace DiscoveryDesign\FilamentGaze;

use DateTimeInterface;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class Gaze
 *
 * Central helper for reading and writing the viewer list that backs the
 * GazeBanner and GazeColumn. Owns the cache key format, the expiry rules and
 * the auth guard resolution so that all components agree on them.
 */
final class Gaze
{
    public const CACHE_PREFIX = 'filament-gaze-';

    /**
     * Resolve the identifier used to store viewers against a record.
     *
     * A string is passed straight through, so callers that already hold a
     * custom identifier (see GazeBanner::identifier()) can use every helper.
     */
    public static function getIdentifier(Model | string $record): string
    {
        if (is_string($record)) {
            return $record;
        }

        return get_class($record) . '-' . $record->getKey();
    }

    public static function getCacheKey(Model | string $record): string
    {
        return self::CACHE_PREFIX . self::getIdentifier($record);
    }

    /**
     * Every viewer stored against the record, including expired entries, the
     * current user, and viewers belonging to other auth guards.
     */
    public static function getRawViewers(Model | string $record): array
    {
        return Cache::get(self::getCacheKey($record), []);
    }

    public static function putViewers(Model | string $record, array $viewers, DateTimeInterface $expires): void
    {
        Cache::put(self::getCacheKey($record), $viewers, $expires);
    }

    /**
     * Viewers that are still active and belong to the current auth guard.
     */
    public static function getViewers(Model | string | null $record, bool $excludeCurrentUser = true): array
    {
        if (! $record) {
            return [];
        }

        $authGuard = self::getAuthGuard();
        $currentUserId = self::getCurrentUserId();

        $viewers = [];

        foreach (self::getRawViewers($record) as $viewer) {
            if (! isset($viewer['expires']) || Carbon::parse($viewer['expires'])->isPast()) {
                continue;
            }

            // Viewers stored before guards were tracked have no guard, so are kept.
            if (isset($viewer['guard']) && $viewer['guard'] !== $authGuard) {
                continue;
            }

            if ($excludeCurrentUser && isset($viewer['id']) && $viewer['id'] == $currentUserId) {
                continue;
            }

            $viewers[] = $viewer;
        }

        return $viewers;
    }

    public static function isOpened(Model | string | null $record, bool $excludeCurrentUser = true): bool
    {
        return self::getViewerCount($record, $excludeCurrentUser) > 0;
    }

    public static function getViewerCount(Model | string | null $record, bool $excludeCurrentUser = true): int
    {
        return count(self::getViewers($record, $excludeCurrentUser));
    }

    public static function isLockedByOther(Model | string | null $record): bool
    {
        foreach (self::getViewers($record) as $viewer) {
            if ($viewer['has_control'] ?? false) {
                return true;
            }
        }

        return false;
    }

    public static function getAuthGuard(): string
    {
        return Filament::getCurrentPanel()?->getAuthGuard() ?? 'web';
    }

    public static function getCurrentUserId(): mixed
    {
        return auth()->guard(self::getAuthGuard())->id();
    }
}
