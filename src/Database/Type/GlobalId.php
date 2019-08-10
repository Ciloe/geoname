<?php

namespace App\Database\Type;

use Allocine\Titania\Type\Base\ConstrainedObject;

class GlobalId extends ConstrainedObject
{
    /**
     * @return array
     */
    public static function getAttributeDefinition()
    {
        return [
            'schema' => null,
            'table' => null,
            'id' => null
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
                $args = [
                    'schema' => $args[0][0],
                    'table' => $args[0][1],
                    'id' => $args[0][2],
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
        return sprintf(
            '%s.%s.%s',
            $this->schema,
            $this->table,
            $this->id
        );
    }

    /**
     * @return string[]
     */
    public function toLTreeArray(): array
    {
        return [$this->schema, $this->table, $this->id];
    }
}
