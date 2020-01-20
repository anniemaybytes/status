<?php declare(strict_types=1);

namespace Status\Cache;

use APCUIterator;

/**
 * Class Apc
 *
 * @package Status\Cache
 */
class Apc implements IKeyStore
{
    /**
     * @var string $keyPrefix
     */
    private $keyPrefix;

    /**
     * @var bool $clearOnGet
     */
    private $clearOnGet = false;

    /**
     * @var array $cacheHits
     */
    private $cacheHits = [];

    /**
     * @var int $time
     */
    private $time = 0;

    /**
     * Apc constructor.
     *
     * @param string $keyPrefix
     */
    public function __construct(string $keyPrefix)
    {
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
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
     * @return bool|string[]
     */
    private function exists(string $key)
    {
        return apcu_exists($key);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expiry
     *
     * @return array|bool
     */
    public function doSet(string $key, $value, int $expiry = 3600)
    {
        $start = $this->startCall();
        $key = $this->keyPrefix . $key;

        $res = apcu_add($key, $value, $expiry);

        $this->endCall($start);

        return $res;
    }

    /**
     * @param string $key
     *
     * @return bool|string[]
     */
    public function doDelete(string $key)
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
    public function getExecutionTime(): int
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
    private function endCall(float $start)
    {
        $this->time += (microtime(true) - $start) * 1000;
    }

    /** {@inheritDoc} */
    public function doTouch(string $key, int $expiry = 10800)
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

    /** {@inheritDoc} */
    public function doFlush()
    {
        apcu_clear_cache();
    }

    /** {@inheritDoc} */
    public function getAllKeys(): array
    {
        $keys = [];
        foreach (new APCUIterator('/.+/') as $counter) {
            array_push($keys, $counter['key']);
        }
        return $keys;
    }

    /** {@inheritDoc} */
    public function setClearOnGet(bool $val)
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
