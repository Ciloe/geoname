<?php

namespace App\Logger;

use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Type;

class ESLogger
{
    const STORAGE_SUCCESS_KEY = 'success';
    const STORAGE_ERROR_KEY = 'error';

    /**
     * @var array
     */
    private $storage = [
        self::STORAGE_SUCCESS_KEY => [],
        self::STORAGE_ERROR_KEY => [],
    ];

    /**
     * @param Type $index
     * @param Query $query
     * @param ResultSet $resultSet
     *
     * @return EsLogger
     */
    public function addSuccess(Type $index, Query $query, ResultSet $resultSet): self
    {
        $this->storage[self::STORAGE_SUCCESS_KEY][] = [
            'index' => $index,
            'query' => $query,
            'resultSet' => $resultSet
        ];

        return $this;
    }

    /**
     * @param Type $index
     * @param Query $query
     * @param \Exception $e
     *
     * @return EsLogger
     */
    public function addError(Type $index, Query $query, \Exception $e): self
    {
        $this->storage[self::STORAGE_ERROR_KEY][] = [
            'index' => $index,
            'query' => $query,
            'exception' => $e,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getStorage(): array
    {
        return $this->storage;
    }
}
