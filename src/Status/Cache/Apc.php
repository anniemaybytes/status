<?php

declare(strict_types=1);

namespace Status\Cache;

use APCUIterator;
use Throwable;
use Tracy\Debugger;

/**
 * Class Apc
 *
 * @package Status\Cache
 */
final class Apc implements IKeyStore
{
    private bool $clearOnGet = false;

    private array $cacheHits = [];
    private float $time = 0;

    public function __construct()
    {
        $bar = new CacheTracyBarPanel($this);

        Debugger::getBar()->addPanel($bar);
        Debugger::getBlueScreen()->addPanel(
            static function (?Throwable $e) use ($bar) {
                if ($e) {
                    return null;
                }
                return [
                    'tab' => 'Apc hits',
                    'panel' => $bar->getPanel(),
                ];
            }
        );
    }

    // === CACHE ===

    /** @inheritDoc */
    public function get(string $key): mixed
    {
        $start = $this->startCall();

        if ($this->clearOnGet) {
            $this->delete($key);
            $this->endCall($start);
            return false;
        }

        if ($this->exists($key)) {
            $res = apcu_fetch($key);
            $this->cacheHits[$key] = $res;
        } else {
            $res = false;
        }

        $this->endCall($start);

        return $res;
    }

    public function set(string $key, mixed $value, int $time = 3600): bool
    {
        $start = $this->startCall();
        $res = apcu_add($key, $value, $time);
        $this->endCall($start);

        return $res;
    }

    public function delete(string $key): bool
    {
        $start = $this->startCall();

        $res = apcu_delete($key);
        if ($res) {
            $this->cacheHits[$key] = $res;
        }

        $this->endCall($start);

        return $res;
    }

    public function increment(string $key, int $n = 1, mixed $initial = null, int $expiry = 0): bool|int
    {
        $start = $this->startCall();

        if ($this->clearOnGet) {
            $this->endCall($start);
            return $initial;
        }

        if (!$this->exists($key)) {
            apcu_add($key, $initial, $expiry);
            $res = $initial;
        } elseif (is_int(apcu_fetch($key))) {
            $res = apcu_inc($key, $n);
        } else {
            $this->endCall($start);
            return false;
        }

        $this->endCall($start);

        return $res;
    }

    public function touch(string $key, int $expiry = 10800): bool
    {
        $start = $this->startCall();

        if ($this->exists($key)) {
            $value = apcu_fetch($key);
            apcu_delete($key);
            $res = apcu_add($key, $value, $expiry);
        } else {
            $res = false;
        }

        $this->endCall($start);

        return $res;
    }

    public function flush(): void
    {
        apcu_clear_cache();
    }

    public function setClearOnGet(bool $val): void
    {
        $this->clearOnGet = $val;
    }

    // === STATISTICS ===

    public function getAllKeys(): array
    {
        $keys = [];
        foreach (new APCUIterator('/.+/') as $counter) {
            $keys[] = $counter['key'];
        }
        return $keys;
    }

    public function getStats(): array
    {
        return apcu_cache_info(true) ?: [];
    }

    public function getCacheHits(): array
    {
        return $this->cacheHits;
    }

    public function getExecutionTime(): float
    {
        return $this->time;
    }

    // === HELPER ===

    private function exists(string $key): bool
    {
        return apcu_exists($key);
    }

    private function startCall(): float
    {
        return microtime(true);
    }

    private function endCall(float $start): void
    {
        $this->time += (microtime(true) - $start) * 1000;
    }
}
