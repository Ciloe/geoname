<?php

namespace App\Resolver;

use App\Database\Model\Geoname\ExecutiveSchema\Localization;
use App\Database\Model\Geoname\ExecutiveSchema\LocalizationModel;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use PommProject\Foundation\Where;

final class LocalizationResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var LocalizationModel
     */
    private $model;

    /**
     * @param LocalizationModel $model
     */
    public function __construct(LocalizationModel $model)
    {
        $this->model = $model;
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
     * @return Localization|null
     */
    public function resolveLocalization(string $uuid): ?Localization
    {
        $localizations = $this->model->findWhere('uuid = $*', [$uuid]);

        return !$localizations->isEmpty() ? $localizations->current() : null;
    }

    /**
     * @param Argument $args
     *
     * @return object|\Overblog\GraphQLBundle\Relay\Connection\Output\Connection
     */
    public function resolveList(Argument $args)
    {
        $where = Where::create();

        if (isset($args['search'])) {
            $where->andWhere('name', [sprintf('LIKE %%s%', $args['search'])]);
        }

        if (isset($args['isActive'])) {
            $where->andWhere('active', [$args['isActive']]);
        }

        $paginator = new Paginator(function ($limit, $offset) use ($where) {
            return iterator_to_array($this->model->findAllWithPagination($where, $limit, $offset));
        }, Paginator::MODE_REGULAR);

        return $paginator->auto($args, function() use ($where) {
            return $this->model->countWhere($where);
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function getAliases(): array
    {
        return [
            'resolveLocalizationType' => 'LocalizationType',
            'resolveLocalization' => 'Localization',
            'resolveList' => 'Localizations',
        ];
    }
}
