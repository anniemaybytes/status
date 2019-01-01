<?php
namespace myApp\Cache;

class Apc implements IKeyStore
{
    public function fetch($key)
    {
        return apcu_fetch($key);
    }

    public function exists($key)
    {
        return apcu_exists($key);
    }

    public function add($key, $value, $expiry = 3600)
    {
        return apcu_add($key, $value, $expiry);
    }

    public function delete($key)
    {
        return apcu_delete($key);
    }
}
