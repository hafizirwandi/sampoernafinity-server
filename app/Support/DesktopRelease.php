<?php

namespace App\Support;

/**
 * Reads the latest Windows desktop release straight from the latest.yml that
 * electron-builder generates, so the landing page, the /download redirect,
 * and the auto-updater can never disagree about which build is current.
 */
class DesktopRelease
{
    /**
     * @return array{version: string, file: string, url: string, size: int|null, released_at: string|null}|null
     */
    public static function latest(): ?array
    {
        $folder = trim(config('desktop.release_path'), '/');
        $manifest = public_path($folder.'/latest.yml');

        if (! is_file($manifest)) {
            return null;
        }

        $yaml = (string) file_get_contents($manifest);

        // latest.yml is a small, fixed-shape file; only the top-level keys are
        // needed here, so a line match avoids pulling in a YAML parser.
        $version = self::topLevelValue($yaml, 'version');
        $file = self::topLevelValue($yaml, 'path');

        if (! $version || ! $file || ! is_file(public_path($folder.'/'.$file))) {
            return null;
        }

        return [
            'version' => $version,
            'file' => $file,
            'url' => asset($folder.'/'.rawurlencode($file)),
            'size' => filesize(public_path($folder.'/'.$file)) ?: null,
            'released_at' => self::topLevelValue($yaml, 'releaseDate'),
        ];
    }

    public static function minVersion(): ?string
    {
        return config('desktop.min_version') ?: null;
    }

    /**
     * True when the given app version is below the configured minimum.
     */
    public static function isUnsupported(string $version): bool
    {
        $min = self::minVersion();

        return $min !== null && version_compare(self::normalize($version), self::normalize($min), '<');
    }

    private static function topLevelValue(string $yaml, string $key): ?string
    {
        if (! preg_match('/^'.preg_quote($key, '/').':\s*[\'"]?([^\'"\r\n]+?)[\'"]?\s*$/m', $yaml, $match)) {
            return null;
        }

        return $match[1];
    }

    // Strip a leading "v" and any pre-release suffix so "v1.2.0-beta.1"
    // compares as 1.2.0.
    private static function normalize(string $version): string
    {
        return preg_replace('/[-+].*$/', '', ltrim(trim($version), 'vV'));
    }
}
