<?php

namespace App\Database\Converter;

class DIConverter
{
    /**
     * @var BaseConverter[]
     */
    private $converters = [];

    /**
     * @param BaseConverterInterface $converter
     * @param string|null $alias
     */
    public function addConverter(BaseConverterInterface $converter, string $alias = null)
    {
        if (is_null($alias)) {
            $alias = get_class($converter);
            if (!is_null($converter->getName())) {
                $alias = $converter->getName();
            }
        }

        $this->converters[$alias] = $converter;
    }

    /**
     * @return BaseConverter[]
     */
    public function getConverters(): array
    {
        return $this->converters;
    }
}
