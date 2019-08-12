<?php

namespace App\Database\Converter;

use PommProject\Foundation\Converter\ConverterInterface;
use PommProject\Foundation\Session\Session;

class Geometry implements ConverterInterface, BaseConverterInterface
{
    use BaseConverter;

    public function __construct()
    {
        $this->setName('Geometry')
            ->setType('public.geometry');
    }

    public function getConverter(): ConverterInterface
    {
        return $this;
    }

    public function fromPg($data, $type, Session $session)
    {
        if ((trim($data) === '') || is_null($data)) {
            return null;
        }

        $result = $session->getConnection()->executeAnonymousQuery(sprintf(
            'SELECT ST_AsGeoJSon(%s::public.geometry) AS coords',
            $session->getConnection()->escapeLiteral($data)
        ));

        if (! $result) {
            return null;
        }

        return json_decode($result->fetchRow(0)['coords']);
    }

    public function toPg($data, $type, Session $session)
    {
        return $this->toPgStandardFormat($data, $type, $session);
    }

    public function toPgStandardFormat($data, $type, Session $session)
    {
        $result = $session->getConnection()->executeAnonymousQuery(sprintf(
            "SELECT ST_GeomFromGeoJSON(%s) AS geometry",
            json_encode($data)
        ));

        if (! $result) {
            return null;
        }

        return sprintf(
            "%s",
            $result->fetchRow(0)['geometry']
        );
    }
}
