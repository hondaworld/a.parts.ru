<?php


namespace App\Model\Manager\Entity\Manager;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class PhonemobType extends StringType
{
    public const NAME = 'manager_phonemob';

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return preg_replace('/[^0-9+]/', '', $value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value;
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