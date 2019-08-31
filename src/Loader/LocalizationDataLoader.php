<?php

namespace App\Loader;

use App\Cache\CacheMap;
use App\Database\Model\Geoname\ExecutiveSchema\LocalizationModel;
use Overblog\PromiseAdapter\PromiseAdapterInterface;

class LocalizationDataLoader extends AbstractDataLoader
{
    const LOCALIZATION_CACHE_KEY = 'localization-';

    /**
     * @var LocalizationModel
     */
    private $localizationModel;

    /**
     * @var CacheMap
     */
    private $cacheMap;

    /**
     * @param LocalizationModel $localizationModel
     * @param PromiseAdapterInterface $promiseAdapter
     * @param CacheMap $cacheMap
     */
    public function __construct(
        LocalizationModel $localizationModel,
        PromiseAdapterInterface $promiseAdapter,
        CacheMap $cacheMap
    ) {
        parent::__construct($promiseAdapter);
        $this->localizationModel = $localizationModel;
        $this->cacheMap = $cacheMap;
    }

    /**
     * @inheritdoc
     */
    protected function find(array $ids): array
    {
        return array_map(function ($id) {
            if ($this->cacheMap->has(self::LOCALIZATION_CACHE_KEY . $id)) {
                return $this->cacheMap->get(self::LOCALIZATION_CACHE_KEY . $id);
            }
            if (is_string($id)) {
                $localizations = $this->localizationModel->findWhere('uuid = $*', [$id]);
                $localization = !$localizations->isEmpty() ? $localizations->current() : null;
            } else {
                $localization = $this->localizationModel->findByPK(['id' => $id]);
            }

            if (!is_null($localization)) {
                $this->cacheMap->set(self::LOCALIZATION_CACHE_KEY . $id, $localization);
            }

            return $localization;
        }, $ids);
    }
}
