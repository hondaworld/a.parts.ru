<?php


namespace App\Model\Auto\Entity\Auto;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class AutoNumberType extends StringType
{
    public const NAME = 'auto_number';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof AutoNumber ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new AutoNumber($value);
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