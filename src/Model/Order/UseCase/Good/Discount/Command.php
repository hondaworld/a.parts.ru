<?php

namespace App\Model\Order\UseCase\Good\Discount;

use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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

    public $cols;
}
