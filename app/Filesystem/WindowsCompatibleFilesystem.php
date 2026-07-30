<?php

namespace App\Filesystem;

use Illuminate\Filesystem\Filesystem;

class WindowsCompatibleFilesystem extends Filesystem
{
    /**
     * Write the contents of a file, replacing it atomically if possible.
     * Overridden to handle Windows transient file locking (Access Denied / code 5).
     *
     * @param  string  $path
     * @param  string  $content
     * @param  int|null  $mode
     * @return void
     */
    public function replace($path, $content, $mode = null)
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            parent::replace($path, $content, $mode);
            return;
        }

        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        if (! is_null($mode)) {
            @chmod($tempPath, $mode);
        } else {
            @chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        // Windows-friendly retry loop for rename
        $retries = 10;
        $delay = 15000; // 15ms in microseconds

        for ($i = 0; $i < $retries; $i++) {
            try {
                // On Windows, if destination exists, we try to delete it first if possible
                if (file_exists($path)) {
                    @chmod($path, 0777);
                    @unlink($path);
                }

                if (@rename($tempPath, $path)) {
                    return;
                }
            } catch (\Throwable $e) {
                // Ignore transient errors and retry
            }
            usleep($delay);
        }

        // Final fallback: standard rename which will report standard error if still failing
        rename($tempPath, $path);
    }
}
