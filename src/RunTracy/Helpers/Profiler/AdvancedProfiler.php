<?php

/** @noinspection PropertyInitializationFlawsInspection */

declare(strict_types=1);

namespace RunTracy\Helpers\Profiler;

/**
 * Advanced PHP class for profiling
 *
 * @author Petr Knap <dev@petrknap.cz>
 * @package RunTracy\Helpers\Profiler
 */
class AdvancedProfiler extends SimpleProfiler
{
    protected static bool $enabled = false;

    /**
     * @var Profile[]
     */
    protected static array $stack = [];

    /**
     * @var callable
     */
    protected static $postProcessor;

    /**
     * Set post processor
     *
     * Post processor is callable with one input argument (return from finish method)
     * and is called at the end of finish method.
     */
    public static function setPostProcessor(callable $postProcessor): void
    {
        self::$postProcessor = $postProcessor;
    }

    public static function start(?string $labelOrFormat = null, mixed $args = null, mixed $opt = null): bool
    {
        if (self::$enabled) {
            if ($labelOrFormat === null) {
                $labelOrFormat = self::getCurrentFileHashLine(1);
                $args = null;
                $opt = null;
            }

            return parent::start($labelOrFormat, $args, $opt);
        }

        return false;
    }

    /**
     * Get current "{file}#{line}"
     */
    public static function getCurrentFileHashLine(): bool|string
    {
        $args = func_get_args();

        $deep = &$args[0];

        $backtrace = debug_backtrace();
        $backtrace = &$backtrace[$deep ?: 0];

        if ($backtrace) {
            return sprintf(
                '%s#%s',
                $backtrace['file'],
                $backtrace['line']
            );
        }

        return false;
    }

    public static function finish(?string $labelOrFormat = null, mixed $args = null, mixed $opt = null): Profile|bool
    {
        if (self::$enabled) {
            if ($labelOrFormat === null) {
                $labelOrFormat = self::getCurrentFileHashLine(1);
                $args = null;
                $opt = null;
            }

            $profile = parent::finish($labelOrFormat, $args, $opt);

            if (self::$postProcessor === null) {
                return $profile;
            }

            return call_user_func(self::$postProcessor, $profile);
        }

        return false;
    }
}
