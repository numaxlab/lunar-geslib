<?php

namespace Tests\Dummies\Cegal;

use NumaxLab\Cegal\Dto\BookAvailability;
use SimpleXMLElement;

readonly class FakeBookAvailability extends BookAvailability
{
    public static function createFake(
        string $isbn = '9780000000000',
        string $sinliId = 'SIN-A',
        string $name = 'Fake name',
        string $type = 'D',
        string $address = 'Fake address',
        string $postalCode = '12345',
        string $municipality = 'Fake municipality',
        string $province = 'Fake province',
        string $contactPerson = 'Fake person',
        string $phoneNumber = '0000000000',
        string $email = 'fake@email.com',
        string $website = 'https://fake.com',
        string $countryIsoCode = 'ES',
        string $timestamp = '2022-01-01 00:00:00',
    ): BookAvailability {
        $xml = new SimpleXMLElement(
            '<LIBRO></LIBRO>',
        );
        $xml->addChild('ID_SINLI_ASOCIADO', $sinliId);
        $xml->addChild('NOMBREA_ASOCIADO', $name);
        $xml->addChild('TIPO_ASOCIADO', $type);
        $xml->addChild('DIRECCION', $address);
        $xml->addChild('CODIGO_POSTAL', $postalCode);
        $xml->addChild('LOCALIDAD', $municipality);
        $xml->addChild('PROVINCIA', $province);
        $xml->addChild('PERSONA_CONTACTO', $contactPerson);
        $xml->addChild('TELEFONO', $phoneNumber);
        $xml->addChild('EMAIL', $email);
        $xml->addChild('WEB', $website);
        $xml->addChild('PAIS_ISO', $countryIsoCode);
        $xml->addChild('FECHA_COMUNICACION', $timestamp);

        return static::fromXml($xml);
    }
}
