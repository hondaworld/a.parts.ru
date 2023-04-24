<?php


namespace App\Model;


use App\Model\Card\Entity\Card\DetailNumber;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class DetailNumberType extends StringType
{
    public const NAME = 'detail_number';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value instanceof DetailNumber ? $value->getValue() : $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new DetailNumber($value);
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