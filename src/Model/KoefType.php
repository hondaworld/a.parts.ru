<?php


namespace App\Model;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DecimalType;

class KoefType extends DecimalType
{
    public const NAME = 'koef';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value ? str_replace(',', '.', $value) : 0;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value * 1;
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