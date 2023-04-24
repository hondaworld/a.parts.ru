<?php


namespace App\Model\Manager\Entity\Manager;



class Email
{
    private $value;

    public function __construct(?string $value)
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('E-mail некорректный');
        }
        $this->value = mb_strtolower($value);
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