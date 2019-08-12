<?php

namespace App\Database\Model\Geoname\ExecutiveSchema;

use PommProject\ModelManager\Model\Model;
use PommProject\ModelManager\Model\Projection;
use PommProject\ModelManager\Model\ModelTrait\WriteQueries;

use PommProject\Foundation\Where;

use App\Database\Model\Geoname\ExecutiveSchema\AutoStructure\Localization as LocalizationStructure;
use App\Database\Model\Geoname\ExecutiveSchema\Localization;

/**
 * LocalizationModel
 *
 * Model class for table localization.
 *
 * @see Model
 */
class LocalizationModel extends Model
{
    use WriteQueries;

    /**
     * __construct()
     *
     * Model constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->structure = new LocalizationStructure;
        $this->flexible_entity_class = Localization::class;
    }

    /**
     * @param Where $where
     * @param int $offset
     * @param int $limit
     *
     * @return \PommProject\ModelManager\Model\CollectionIterator
     */
    public function findAllWithPagination(Where $where, int $offset, int $limit)
    {
        $sql = $this->getFindWhereSql($where, $this->createProjection());

        return $this->query(
            sprintf("%s offset %d limit %d", $sql, $offset, $limit),
            $where->getValues(),
            $this->createProjection()
        );
    }
}
