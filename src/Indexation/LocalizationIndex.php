<?php

namespace App\Indexation;

use App\Logger\ESLogger;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;

class LocalizationIndex
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ESLogger
     */
    private $logger;

    /**
     * @param Client $client
     * @param EsLogger $logger
     */
    public function __construct(Client $client, EsLogger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * @param Query $query
     *
     * @return ResultSet
     * @throws \Exception
     */
    public function search(Query $query): ResultSet
    {
        $index = $this->client->getIndex(LocalizationBuilder::INDEX_NAME)
            ->getType(LocalizationBuilder::INDEX_TYPE)
        ;

        try {
            $resultSet = $index->search($query);
            $this->logger->addSuccess($index, $query, $resultSet);
        } catch (\Exception $e) {
            $this->logger->addError($index, $query, $e);

            throw $e;
        }

        return $resultSet;
    }

    /**
     * @param Query $query
     *
     * @return int
     */
    public function count(Query $query): int
    {
        $index = $this->client->getIndex(LocalizationBuilder::INDEX_NAME)
            ->getType(LocalizationBuilder::INDEX_TYPE)
        ;

        return $index->count($query);
    }

    /**
     * @param ResultSet $resultSet
     *
     * @return array
     */
    public function getResultSetIds(ResultSet $resultSet): array
    {
        $ids = [];
        $documents = $resultSet->getDocuments();

        /** @var Document $document */
        foreach ($documents as $document) {
            $ids[] = $document->getId();
        }

        return $ids;
    }
}
