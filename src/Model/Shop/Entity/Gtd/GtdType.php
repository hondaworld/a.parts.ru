<?php


namespace App\Model\Shop\Entity\Gtd;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class GtdType extends StringType
{
    public const NAME = 'gtd';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof Gtd ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new Gtd($value);
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