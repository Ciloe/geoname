<?php

namespace App\Database\Converter;

use App\Database\Type\Tag3DCollection;
use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Converter\PgArray;
use PommProject\Foundation\Session\Session;

class Tag3DArray extends PgArray implements BaseConverterInterface
{
    use BaseConverter;

    public function __construct()
    {
        $this->setName('Tag3DArray')
            ->setType('public.tag3d_array');
    }

    /**
     * @inheritdoc
     */
    public function getConverter(): ?ConverterInterface
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getSubType($type)
    {
        return 'public.tag3d';
    }

    /**
     * @inheritdoc
     */
    public function fromPg($data, $type, Session $session)
    {
        $tag3DArray = parent::fromPg($data, $type, $session);

        return new Tag3DCollection($tag3DArray);
    }

    /**
     * @inheritdoc
     */
    public function toPgStandardFormat($data, $type, Session $session)
    {
        $tags = [];

        foreach ($data as $index => $tag) {
            if (!is_array($tag)) {
                $tags[] = $tag->toArray();
            } else {
                $tags[] = $tag;
            }
        }

        return parent::toPgStandardFormat($tags, $type, $session);
    }
}
