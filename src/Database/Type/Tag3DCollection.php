<?php

namespace App\Database\Type;

use Allocine\Titania\Type\Base\ObjectCollection;

class Tag3DCollection extends ObjectCollection
{
    protected $internalObjectClass = Tag3D::class;

    /**
     * Construct a new array object
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        parent::__construct($data ?? []);
    }

    /**
     * @return array
     */
    public function toStringArray(): array
    {
        return array_map(
            function (Tag3D $value) {
                return $value->__toString();
            },
            $this->toArray()
        );
    }

    /**
     * @param Tag3D $tag
     *
     * @return bool
     * @throws \Exception
     */
    public function contains($tag): bool
    {
        return in_array(
            $tag,
            $this->toArray()
        );
    }

    /**
     * @param Tag3D $tag
     *
     * @return Tag3DCollection
     * @throws \Exception
     */
    public function append($tag): self
    {
        if (!$this->contains($tag)) {
            parent::append($tag);
        }

        return $this;
    }

    /**
     * @param Tag3D $tag
     *
     * @return Tag3DCollection
     * @throws \Exception
     */
    public function remove(Tag3D $tag): self
    {
        foreach ($this as $i => $t) {
            if ($t == $tag) {
                $this->offsetUnset($i);
                continue;
            }
        }

        return $this;
    }

    /**
     * @param array $tags
     *
     * @return Tag3DCollection
     * @throws \Exception
     */
    public function merge(array $tags): self
    {
        foreach (new Tag3DCollection($tags) as $t) {
            $this->append($t);
        }

        return $this;
    }

    /**
     * @return Tag3DCollection
     */
    public function empty(): self
    {
        $this->exchangeArray([]);

        return $this;
    }
}