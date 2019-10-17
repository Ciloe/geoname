<?php

namespace App\Cache;

use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\TraceableAdapter;

class CacheMap
{
    /**
     * @var AbstractAdapter
     */
    private $adapter;

    /**
     * @param AbstractAdapter $adapter
     */
    public function __construct(TraceableAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param $key
     *
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($key)
    {
        $key = self::serializedKey($key);

        return $this->adapter->getItem($key)->get();
    }

    /**
     * @param $key
     *
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function has($key): bool
    {
        return $this->adapter->hasItem(self::serializedKey($key));
    }

    /**
     * @param $key
     * @param $object
     *
     * @return CacheMap
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function set($key, $object): self
    {
        $key = self::serializedKey($key);
        $item = $this->adapter->getItem($key);
        $item->set($object);
        $this->adapter->save($item);

        return $this;
    }

    /**
     * @param $key
     *
     * @return CacheMap
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function clear($key): self
    {
        $this->adapter->delete(self::serializedKey($key));

        return $this;
    }

    /**
     * @return $this
     */
    public function clearAll(): self
    {
        $this->adapter->clear();

        return $this;
    }

    /**
     * @param mixed $key
     *
     * @return int|string
     */
    private static function serializedKey($key)
    {
        if (is_object($key)) {
            return spl_object_hash($key);
        } elseif (is_array($key)) {
            return json_encode($key);
        }

        return $key;
    }
}
