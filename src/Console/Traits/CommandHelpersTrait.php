<?php

namespace SquirrelForge\Laravel\CoreSupport\Console\Traits;

use Symfony\Component\Console\Output\OutputInterface;

trait CommandHelpersTrait
{
    /** @var int|null $verbosity_level */
    protected ?int $verbosity_level = null;

    /**
     * Check verbosity, default verbose
     * @url https://stackoverflow.com/questions/27611213/using-verbose-in-laravel-artisan-commands
     * @param int|null $level
     * @return bool
     */
    protected function isVerbose(?int $level): bool
    {
        if (!isset($this->verbosity_level)) {
            $this->verbosity_level = $this->getOutput()->getVerbosity();
        }
        return $this->verbosity_level >= ($level ?? OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * Write to console depending on verbosity
     * @param string $message
     * @param int|null $level
     * @param string $method
     * @return void
     */
    protected function verboseOutput(string $message, ?int $level = null, string $method = 'warn'): void
    {
        if (!$this->isVerbose($level)) return;
        $this->{$method}($message);
    }
}
