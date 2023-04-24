<?php

namespace App\Model\Order\UseCase\Good\Create;

use App\Model\Order\Entity\Order\Order;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $orderID;

    public $order_add_reasonID;

    public $zapSkladID;

    public $providerPriceID;

    /**
     * @var string
     * @Assert\NotBlank(
     *     message="Номер должен быть заполнен"
     * )
     */
    public $number;

    /**
     * @Assert\NotBlank(
     *     message="Производитель должен быть заполнен"
     * )
     */
    public $createrID;

    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Количество должно быть заполнено"
     * )
     * @Assert\Positive()
     */
    public $quantity = 1;

    public function __construct(?Order $order = null)
    {
        if ($order) {
            $this->orderID = $order->getId();
        }
    }
}
