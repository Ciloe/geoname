<?php

namespace App\Database\Converter;

use App\Database\Type\Tag3D as Tag3DType;
use PommProject\Foundation\Converter\ArrayTypeConverter;
use PommProject\Foundation\Converter\ConverterInterface as PommConverterInterface;
use PommProject\Foundation\Session\Session;

class Tag3D extends ArrayTypeConverter implements BaseConverterInterface
{
    use BaseConverter;

    public function __construct()
    {
        $this->setName('Tag3D')
            ->setType('public.tag3d');
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
        return new Tag3DType(explode('.', $data));
    }

    /**
     * @inheritdoc
     */
    public function toPg($data, $type, Session $session)
    {
        if (is_null($data)) {
            return sprintf("NULL::%s", $type);
        } else {
            return sprintf("ltree '%s'", $data->__toString());
        }
    }

    /**
     * @inheritdoc
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        if (is_null($data)) {
            return null;
        } else {
            return $data;
        }
    }
}
