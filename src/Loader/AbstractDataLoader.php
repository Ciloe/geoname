<?php

namespace App\Loader;

use Overblog\DataLoader\DataLoader;
use Overblog\PromiseAdapter\PromiseAdapterInterface;

abstract class AbstractDataLoader extends DataLoader
{
    /**
     * @param PromiseAdapterInterface $promiseAdapter
     */
    public function __construct(PromiseAdapterInterface $promiseAdapter) {
        parent::__construct(
            function ($ids) use ($promiseAdapter) {
                return $promiseAdapter->createAll($this->find($ids));
            },
            $promiseAdapter
        );
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    abstract protected function find(array $ids): array;
}
