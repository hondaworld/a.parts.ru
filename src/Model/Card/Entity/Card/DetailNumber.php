<?php


namespace App\Model\Card\Entity\Card;


use Webmozart\Assert\Assert;

class DetailNumber
{
    private $value;

    public function __construct(?string $value)
    {
//        Assert::maxLength($value, 30, 'Номер должен быть не больше 30 символов');
        $this->value = $value ? strtoupper(preg_replace("/[\-\.\ \'\"\=]/", "", trim($value))) : '';
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getHideValue(): string
    {
        return substr_replace($this->value, str_repeat('*', strlen($this->value) - 3), 2, strlen($this->value) - 3);
    }

    public function isEqual(self $other): bool
    {
        return $this->getValue() === $other->getValue();
    }
}