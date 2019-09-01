<?php

namespace App\Resolver;

use App\Database\Model\Geoname\ExecutiveSchema\Localization;
use App\Indexation\LocalizationIndex;
use App\Loader\LocalizationDataLoader;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\ResultSet;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

final class LocalizationResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var LocalizationIndex
     */
    private $index;

    /**
     * @var LocalizationDataLoader
     */
    private $dataLoader;

    /**
     * @var ResultSet[]
     */
    private $resultSets = [];

    /**
     * @param LocalizationIndex $index
     * @param LocalizationDataLoader $dataLoader
     */
    public function __construct(LocalizationIndex $index, LocalizationDataLoader $dataLoader)
    {
        $this->index = $index;
        $this->dataLoader = $dataLoader;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function resolveLocalizationType(string $type): string
    {
        return sprintf('Geometry%s', $type);
    }

    /**
     * @param string $uuid
     *
     * @return Promise|Localization|null
     */
    public function resolveLocalization(string $uuid): Promise
    {
        return $this->dataLoader->load($uuid);
    }

    /**
     * @param int|null $id
     *
     * @return Promise|Localization|null
     */
    public function resolveParent(?int $id): ?Promise
    {
        if (is_null($id)) {
            return null;
        }

        return $this->dataLoader->load($id);
    }

    /**
     * @param Argument $args
     *
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\Output\Connection
     */
    public function resolveList(Argument $args)
    {
        $queryBuilder = new QueryBuilder();
        $boolQuery = $queryBuilder->query()->bool();

        if (!empty($args['search'])) {
            $matchQuery = $queryBuilder->query()->multi_match()
                ->setQuery($args['search'])
                ->setFields(['name', 'name.search'])
                ->setOperator('and')
                ->setFuzziness('AUTO')
            ;

            $boolQuery->addFilter($matchQuery);
        }

        if (isset($args['hasParent'])) {
            if ($args['hasParent']) {
                $boolQuery->addMust($queryBuilder->query()->exists('parent'));
            } else {
                $boolQuery->addMustNot($queryBuilder->query()->exists('parent'));
            }
        }

        $uniqId = uniqid();
        $query = (new Query())->setQuery($boolQuery);
        $paginator = new Paginator(function ($offset, $limit) use ($query, $uniqId) {
            $query->setFrom($offset)
                ->setSize($limit)
            ;

            $this->resultSets[$uniqId] = $this->index->search($query);
            $ids = $this->index->getResultSetIds($this->resultSets[$uniqId]);

            return $this->dataLoader->loadMany($ids);
        }, Paginator::MODE_PROMISE);

        return $paginator->auto($args, function() use ($query, $uniqId) {
            if (!empty($args['last'])) {
                return $this->index->count($query);
            }

            return $this->resultSets[$uniqId]->getTotalHits();
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getAliases(): array
    {
        return [
            'resolveLocalizationType' => 'LocalizationType',
            'resolveParent' => 'LocalizationParent',
            'resolveLocalization' => 'Localization',
            'resolveList' => 'Localizations',
        ];
    }
}
