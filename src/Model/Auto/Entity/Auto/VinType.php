<?php


namespace App\Model\Auto\Entity\Auto;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class VinType extends StringType
{
    public const NAME = 'vin';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof Vin ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new Vin($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
    {
        return true;
    }
}