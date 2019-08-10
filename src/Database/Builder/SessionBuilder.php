<?php

namespace App\Database\Builder;

use App\Database\Converter\BaseConverter;
use App\Database\Converter\DIConverter;
use PommProject\Foundation\Converter\ConverterPooler;
use PommProject\Foundation\Session\Session;
use PommProject\ModelManager\SessionBuilder AS SessionBuilderManager;

final class SessionBuilder extends SessionBuilderManager
{
    /**
     * @var DIConverter|null
     */
    private $converters = null;

    /**
     * @param DIConverter $converters
     */
    public function setConverters(DIConverter $converters) {
        $this->converters = $converters;
    }

    /**
     * @param Session $session
     *
     * @return SessionBuilder
     * @throws \PommProject\Foundation\Exception\FoundationException
     */
    protected function postConfigure(Session $session): SessionBuilder
    {
        parent::postConfigure($session);

        /** @var $pool ConverterPooler */
        $pool = $session->getPoolerForType('converter');
        $holder = $pool->getConverterHolder();

        /** @var BaseConverter $converter */
        if (!is_null($this->converters)) {
            foreach ($this->converters->getConverters() as $converter) {
                $holder
                    ->registerConverter(
                        $converter->getName(),
                        $converter->getConverter(),
                        [$converter->getType()],
                        $converter->isStrict()
                    )
                    ->addTypeToConverter($converter->getName(), $converter->getType())
                ;
            }
        }

        return $this;
    }
}