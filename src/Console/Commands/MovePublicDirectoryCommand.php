<?php

namespace SquirrelForge\Laravel\CoreSupport\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use function SquirrelForge\Laravel\CoreSupport\joinAndResolvePaths;

class MovePublicDirectoryCommand extends Command
{
    /** @var string $signature The name and signature of the console command. */
    protected $signature = 'sqfcs:mvpub {target} {--cp=}';

    /** @var string $description The console command description. */
    protected $description = 'Moves or links the public directory to a new location';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $messageAbort = 'Move/link laravel public directory command aborted.';

        // Get copy option
        $copyFiles = $this->option('cp');
        if (!empty($copyFiles)) {
            if (in_array(mb_strtolower(trim($copyFiles)), ['everything', 'complete', 'true', 'all', 'yes', 'y', 'on', '1'])) {
                $copyFiles = true;
            } else {
                $copyFiles = explode(',', $copyFiles);
            }
        } else {
            $copyFiles = false;
        }

        // Resolve target path
        $target = $this->argument('target');
        if ($target[0] !== DIRECTORY_SEPARATOR) {
            $root = base_path();
            $target = joinAndResolvePaths($root, $target);
        } else {
            $target = joinAndResolvePaths($target);
        }

        // Require target path
        if (!$this->requireTargetPath($target)) {
            $this->error($messageAbort);
            return;
        }

        // Target path not empty
        if (!$this->warnPathNotEmpty($target)) {
            $this->error($messageAbort);
            return;
        }

        // Copy .htaccess, index.php
        // and link all other public files
        $this->linkOrCopyFiles($target, $copyFiles);

        // Link all folders
        $this->linkOrCopyFolders($target, $copyFiles);

        $this->info('Public directory copied/linked to:');
        $this->line('  ' . $target);
    }

    /**
     * Link or copy public folders
     * @param string $path
     * @param bool|array $copy
     * @return void
     */
    protected function linkOrCopyFolders(string $path, bool|array $copy = false): void
    {
        $publicFolders = File::directories(public_path());
        foreach ($publicFolders as $src) {
            $dirname = basename($src);
            $target = joinAndResolvePaths($path, $dirname);
            File::delete($target);
            if ($copy === true || is_array($copy) && in_array($dirname, $copy)) {
                File::copyDirectory($src, $target);
            } else {
                File::link($src, $target);
            }
        }
    }

    /**
     * Link or copy public files
     * @param string $path
     * @param bool|array $copy
     * @return void
     */
    protected function linkOrCopyFiles(string $path, bool|array $copy = false): void
    {
        /**
         * @type {\Symfony\Component\Finder\SplFileInfo[]}
         */
        $publicFiles = File::files(public_path(), true);
        foreach ($publicFiles as $file) {
            $name = $file->getFilename();
            $src = public_path($name);
            $target = joinAndResolvePaths($path, $name);
            File::delete($target);
            if ($copy === true || is_array($copy) && in_array($name, $copy)) {
                if (preg_match('/\.php$/', $name)) {
                    $relative = $this->getRelativePath($src, $target);
                    $updated = $this->updateRelativePaths($relative, File::get($src));
                    File::put($target, $updated);
                } else {
                    File::copy($src, $target);
                }
            } else {
                File::link($src, $target);
            }
        }
    }

    /**
     * Relative path
     * @param string $src
     * @param string $target
     * @return string
     */
    protected function getRelativePath(string $src, string $target): string
    {
        $parents = [];
        $shared = '';
        for ($i = 0; $i < mb_strlen($src); $i++) {
            if ($src[$i] !== $target[$i]) break;
            $shared .= $src[$i];
        }
        $relSrc = mb_substr(base_path(), mb_strlen($shared));
        $relTarget = explode(DIRECTORY_SEPARATOR, dirname(mb_substr($target, mb_strlen($shared))));
        foreach ($relTarget as $segment) {
            $parents[] = '..';
        }
        return joinAndResolvePaths(implode(DIRECTORY_SEPARATOR, $parents), $relSrc) . DIRECTORY_SEPARATOR;
    }

    /**
     * Update relative paths in index.php
     * @param string $relative
     * @param string $content
     * @return string
     */
    protected function updateRelativePaths(string $relative, string $content): string
    {
        return preg_replace('/\.\.\//', $relative, $content);
    }

    /**
     * Warn user path not empty
     * @param string $target
     * @return bool
     */
    protected function warnPathNotEmpty(string $target): bool
    {
        if (!empty(File::files($target, true))) {
            $message = 'Target directory is not empty, continue anyway? (existing files and directories will be replaced!)';
            if (!$this->confirm($message, true)) return false;
        }
        return true;
    }

    /**
     * Require target directory
     * @param string $target
     * @return bool
     */
    protected function requireTargetPath(string $target): bool
    {
        if (!File::isDirectory($target)) {
            $message = 'The target directory (' . $target . ') does not exist, create it?';
            if (!$this->confirm($message, true)) return false;
            File::makeDirectory($target, 0755, true);
        }
        return true;
    }
}
