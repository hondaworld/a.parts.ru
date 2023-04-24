<?php


namespace App\Model\Shop\Entity\Gtd;


use Webmozart\Assert\Assert;

class Gtd
{
    private $value;

    public function __construct(?string $value)
    {
//        Assert::maxLength($value, 30, 'Номер должен быть не больше 30 символов');
        $this->value = $value ? preg_replace("/[^0-9\/\-]/", "", trim($value)) : '';
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