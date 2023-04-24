<?php

namespace App\Model\Order\UseCase\Good\SmsPay;

use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, шаблон"
     * )
     */
    public $templateID;

    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Укажите номер заказа"
     * )
     * @Assert\Positive (
     *     message="Номер заказа должен быть больше нуля"
     * )
     */
    public $orderID;

    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Укажите сумму заказа"
     * )
     */
    public $sum;
}
