<?php

namespace App\Database\Converter;

use PommProject\Foundation\Converter\ConverterInterface as PommConverterInterface;
use PommProject\Foundation\Converter\PgLtree;

class LTree implements BaseConverterInterface
{
    use BaseConverter;

    public function __construct()
    {
        $this->setName('LTree')
            ->setType('public.ltree');
    }

    /**
     * @inheritdoc
     */
    public function getConverter(): ?PommConverterInterface
    {
        return new PgLtree();
    }
}
