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
    private string $keyPrefix;

    private bool $clearOnGet = false;

    private array $cacheHits = [];
    private float $time = 0;

    public function __construct(string $keyPrefix)
    {
        $this->keyPrefix = $keyPrefix;

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

    public function doGet(string $key): mixed
    {
        $start = $this->startCall();
        $keyOld = $key;
        $key = $this->keyPrefix . $key;

        if ($this->clearOnGet) {
            $this->doDelete($keyOld);
            $this->endCall($start);
            return false;
        }

        if ($this->exists($key)) {
            $res = apcu_fetch($key);
        } else {
            $res = false;
        }

        if ($res) {
            $this->cacheHits[$key] = $res;
        }

        $this->endCall($start);

        return $res;
    }

    private function exists(string $key): bool
    {
        return apcu_exists($key);
    }

    public function doSet(string $key, mixed $value, int $time = 3600): bool
    {
        $start = $this->startCall();
        $key = $this->keyPrefix . $key;

        $res = apcu_add($key, $value, $time);

        $this->endCall($start);

        return $res;
    }

    public function doDelete(string $key): bool
    {
        $start = $this->startCall();
        $key = $this->keyPrefix . $key;

        $res = apcu_delete($key);

        if ($res) {
            $this->cacheHits[$key] = $res;
        }

        $this->endCall($start);

        return $res;
    }

    public function getCacheHits(): array
    {
        return $this->cacheHits;
    }

    public function getExecutionTime(): float
    {
        return $this->time;
    }

    public function doIncrement(string $key, int $n = 1, mixed $initial = null, int $expiry = 0): bool|int
    {
        $start = $this->startCall();
        $key = $this->keyPrefix . $key;

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

    private function startCall(): float
    {
        return microtime(true);
    }

    private function endCall(float $start): void
    {
        $this->time += (microtime(true) - $start) * 1000;
    }

    public function doTouch(string $key, int $expiry = 10800): bool
    {
        $start = $this->startCall();
        $key = $this->keyPrefix . $key;

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

    public function doFlush(): void
    {
        apcu_clear_cache();
    }

    public function getAllKeys(): array
    {
        $keys = [];
        foreach (new APCUIterator('/.+/') as $counter) {
            $keys[] = $counter['key'];
        }
        return $keys;
    }

    public function setClearOnGet(bool $val): void
    {
        $this->clearOnGet = $val;
    }

    public function getStats(): array
    {
        return apcu_cache_info(true) ?? [];
    }
}
