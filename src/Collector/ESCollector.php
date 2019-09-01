<?php

namespace App\Collector;

use App\Logger\ESLogger;
use Elastica\ResultSet;
use Elastica\Type;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ESCollector extends DataCollector
{
    /**
     * @var ESLogger
     */
    private $ESLogger;

    /**
     * @param ESLogger $ESLogger
     */
    public function __construct(ESLogger $ESLogger)
    {
        $this->ESLogger = $ESLogger;
    }

    /**
     * @inheritdoc
     */
    public function collect(Request $request, Response $response, \Exception $exception = null): void
    {
        $this->data = $this->ESLogger->getStorage();
    }

    /**
     * @inheritdoc
     */
    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'app.es_collector';
    }

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->data[ESLogger::STORAGE_ERROR_KEY];
    }

    /**
     * @return int
     */
    public function countError(): int
    {
        return count($this->data[ESLogger::STORAGE_ERROR_KEY]);
    }

    /**
     * @return array
     */
    public function getSuccess(): array
    {
        return $this->data[ESLogger::STORAGE_SUCCESS_KEY];
    }

    /**
     * @return int
     */
    public function countSuccess(): int
    {
        return count($this->data[ESLogger::STORAGE_SUCCESS_KEY]);
    }

    /**
     * @return int
     */
    public function countAll(): int
    {
        return $this->countSuccess() + $this->countError();
    }

    /**
     * @return float
     */
    public function getAllTime(): float
    {
        $time = 0;

        foreach ($this->getSuccess() as $success) {
            /** @var ResultSet[] $success */
            $time += $success['resultSet']->getTotalTime();
        }

        foreach ($this->getError() as $error) {
            /** @var Type[] $error */
            $time += $error['index']->getIndex()->getClient()->getLastResponse()->getQueryTime();
        }

        return $time;
    }
}
