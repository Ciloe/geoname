<?php

namespace App\Database\Type;

use Allocine\Titania\Type\Base\ConstrainedObject;

class Tag3D extends ConstrainedObject
{
    /**
     * @return array
     */
    public static function getAttributeDefinition()
    {
        return [
            'category' => null,
            'subcategory' => null,
            'tag' => null,
        ];
    }

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $args = func_get_args();

        if (count($args) === 1) {
            if (is_array($args[0])) {
                $args =  [
                    'category' => $args[0][0],
                    'subcategory' => $args[0][1],
                    'tag' => $args[0][2],
                ];
            } else {
                $args = $args[0];
            }
        }

        parent::__construct($args);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return implode('.', [
            $this->category,
            $this->subcategory,
            $this->tag
        ]);
    }

    /**
     * @return string[]
     */
    public function toLTreeArray(): array
    {
        return [$this->category, $this->subcategory, $this->tag];
    }
}