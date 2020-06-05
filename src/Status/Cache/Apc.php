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
    /**
     * @var string $keyPrefix
     */
    private string $keyPrefix;

    /**
     * @var bool $clearOnGet
     */
    private bool $clearOnGet = false;

    /**
     * @var array $cacheHits
     */
    private array $cacheHits = [];

    /**
     * @var float $time
     */
    private float $time = 0;

    /**
     * Apc constructor.
     *
     * @param string $keyPrefix
     */
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

    /** {@inheritDoc} */
    public function doGet(string $key)
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

    /**
     * @param string $key
     *
     * @return bool
     */
    private function exists(string $key): bool
    {
        return apcu_exists($key);
    }

    /** {@inheritDoc} */
    public function doSet(string $key, $value, int $expiry = 3600): bool
    {
        $start = $this->startCall();
        $key = $this->keyPrefix . $key;

        $res = apcu_add($key, $value, $expiry);

        $this->endCall($start);

        return $res;
    }

    /** {@inheritDoc} */
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

    /** {@inheritDoc} */
    public function getCacheHits(): array
    {
        return $this->cacheHits;
    }

    /** {@inheritDoc} */
    public function getExecutionTime(): float
    {
        return $this->time;
    }

    /** {@inheritDoc} */
    public function doIncrement(string $key, int $n = 1, $initial = null, int $expiry = 0)
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

    /**
     * @return float
     */
    private function startCall(): float
    {
        return microtime(true);
    }

    /**
     * @param float $start
     */
    private function endCall(float $start): void
    {
        $this->time += (microtime(true) - $start) * 1000;
    }

    /** {@inheritDoc} */
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

    /** {@inheritDoc} */
    public function getAllKeys(): array
    {
        $keys = [];
        foreach (new APCUIterator('/.+/') as $counter) {
            $keys[] = $counter['key'];
        }
        return $keys;
    }

    /** {@inheritDoc} */
    public function setClearOnGet(bool $val): void
    {
        $this->clearOnGet = $val;
    }

    /**
     * @return array
     */
    public function getStats(): array
    {
        return apcu_cache_info(true) ?? [];
    }
}
