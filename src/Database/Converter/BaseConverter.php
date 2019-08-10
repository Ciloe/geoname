<?php

namespace App\Database\Converter;

use PommProject\Foundation\Converter\ConverterInterface;

trait BaseConverter
{
    /**
     * @var string|null
     */
    private $name = null;

    /**
     * @var string|null
     */
    private $type = null;

    /**
     * @var bool
     */
    private $strict = false;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return BaseConverter
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return BaseConverter
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStrict(): bool
    {
        return $this->strict;
    }

    /**
     * @param bool $strict
     *
     * @return BaseConverter
     */
    public function setStrict(bool $strict): self
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * @return ConverterInterface|null
     */
    public function getConverter(): ?ConverterInterface
    {
        return null;
    }
}
