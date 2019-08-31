<?php

namespace App\Resolver;

use App\Database\Model\Geoname\ExecutiveSchema\Localization;
use App\Database\Model\Geoname\ExecutiveSchema\LocalizationModel;
use App\Loader\LocalizationDataLoader;
use GraphQL\Executor\Promise\Promise;
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
     * @var LocalizationDataLoader
     */
    private $dataLoader;

    /**
     * @param LocalizationModel $model
     * @param LocalizationDataLoader $dataLoader
     */
    public function __construct(LocalizationModel $model, LocalizationDataLoader $dataLoader)
    {
        $this->model = $model;
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
    public function resolveParent(?int $id): Promise
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
        $where = Where::create();

        if (isset($args['search'])) {
            $where->andWhere('name', [sprintf('LIKE %%s%', $args['search'])]);
        }

        if (isset($args['isActive'])) {
            $where->andWhere('active', [$args['isActive']]);
        }

        $paginator = new Paginator(function ($limit, $offset) use ($where) {
            $list = $this->model->findAllWithPagination($where, $limit, $offset);

            return $this->dataLoader->loadMany(array_map(function (Localization $localization) {
                return $localization->get('id');
            }, iterator_to_array($list)));
        }, Paginator::MODE_PROMISE);

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
            'resolveParent' => 'LocalizationParent',
            'resolveLocalization' => 'Localization',
            'resolveList' => 'Localizations',
        ];
    }
}
