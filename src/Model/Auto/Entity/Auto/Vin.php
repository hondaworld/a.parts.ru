<?php


namespace App\Model\Auto\Entity\Auto;



class Vin
{
    private $value;

    public function __construct(?string $value)
    {
        $this->value = $value ? mb_strtoupper(str_replace(' ', '', $value)) : '';
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isEqual(self $other): bool
    {
        return $this->getValue() === $other->getValue();
    }
}