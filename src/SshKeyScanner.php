<?php

namespace MattStein\CopySsh;

class SshKeyScanner
{
    /**
     * Default files to ignore when scanning for SSH keys.
     */
    public const DEFAULT_IGNORE = ['.', '..', '.DS_Store', 'authorized_keys', 'config', 'known_hosts'];

    /**
     * Expand a path that starts with ~/ to use the full home directory path.
     */
    public static function expandPath(string $path, string $homeDir): string
    {
        if (str_starts_with($path, '~/')) {
            $path = substr_replace(
                $path,
                rtrim($homeDir, '/') . '/',
                0,
                strlen('~/')
            );
        }

        return $path;
    }

    /**
     * Normalize a directory path to ensure it ends with a trailing slash.
     */
    public static function normalizePath(string $path): string
    {
        return str_ends_with($path, '/') ? $path : $path . '/';
    }

    /**
     * Parse a comma-separated ignore list into an array.
     */
    public static function parseIgnoreList(string $ignoreString): array
    {
        return array_map('trim', explode(',', $ignoreString));
    }

    /**
     * Check if file content appears to be a private key.
     */
    public static function isPrivateKey(string $content): bool
    {
        return str_starts_with($content, '----');
    }

    /**
     * Scan a directory for public SSH keys.
     *
     * @param string $directory The directory to scan
     * @param array $ignore Files to ignore
     * @return array Array of ['filename' => string, 'path' => string]
     */
    public static function scan(string $directory, array $ignore = []): array
    {
        $directory = self::normalizePath($directory);

        if (! is_dir($directory)) {
            return [];
        }

        $files = scandir($directory);
        $options = [];

        foreach ($files as $file) {
            $fullPath = $directory . $file;

            if (is_dir($fullPath)) {
                continue;
            }

            if (in_array($file, $ignore, true)) {
                continue;
            }

            $content = file_get_contents($fullPath);

            if (self::isPrivateKey($content)) {
                continue;
            }

            $options[] = [
                'filename' => $file,
                'path' => $fullPath,
            ];
        }

        return $options;
    }
}
