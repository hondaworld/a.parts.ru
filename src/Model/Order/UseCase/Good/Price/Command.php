<?php

namespace App\Model\Order\UseCase\Good\Price;

use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $goodID;

    /**
     * @var string
     * @Assert\NotBlank(
     *     message="Заполните поле Цена"
     * )
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     * @Assert\Positive (
     *     message="Значение должно быть больше нуля"
     * )
     */
    public $price;


    /**
     * @var string
     * @Assert\NotBlank(
     *     message="Заполните поле Скидка"
     * )
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $discount;

    public function __construct(int $goodID)
    {
        $this->goodID = $goodID;
    }

    public static function fromEntity(OrderGood $orderGood): self
    {
        $command = new self($orderGood->getId());
        $command->price = $orderGood->getPrice();
        $command->discount = $orderGood->getDiscount();
        return $command;
    }
}
