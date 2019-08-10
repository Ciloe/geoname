<?php

namespace App\Database\Converter;

use PommProject\Foundation\Converter\ConverterInterface;

interface BaseConverterInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isStrict(): bool;

    /**
     * @return ConverterInterface|null
     */
    public function getConverter(): ?ConverterInterface;
}
