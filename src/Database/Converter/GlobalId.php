<?php

namespace App\Database\Converter;

use App\Database\Type\GlobalId as GlobalIdType;
use PommProject\Foundation\Converter\ArrayTypeConverter;
use PommProject\Foundation\Converter\ConverterInterface as PommConverterInterface;
use PommProject\Foundation\Session\Session;

class GlobalId extends ArrayTypeConverter implements BaseConverterInterface
{
    use BaseConverter;

    public function __construct()
    {
        $this->setName('GlobalId')
            ->setType('public.global_id');
    }

    /**
     * @inheritdoc
     */
    public function getConverter(): ?PommConverterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function fromPg($data, $type, Session $session)
    {
        return new GlobalIdType(explode('.', $data));
    }

    /**
     * @inheritdoc
     */
    public function toPg($data, $type, Session $session)
    {
        return
            $data !== null
                ? sprintf("ltree '%s'", $data)
                : sprintf("NULL::%s", $type)
            ;
    }

    /**
     * @inheritdoc
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        return implode('.', $data);
    }
}
