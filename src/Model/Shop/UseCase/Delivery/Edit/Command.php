<?php

namespace App\Model\Shop\UseCase\Delivery\Edit;

use App\Model\Shop\Entity\Delivery\Delivery;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $deliveryID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $porog;

    public $x1;

    public $isPercent1;

    public $x2;

    public $isPercent2;

    public $isTK;

    public $isOwnDelivery;

    public $path;

    public $isMain;

    public function __construct(int $deliveryID)
    {
        $this->deliveryID = $deliveryID;
    }

    public static function fromEntity(Delivery $delivery): self
    {
        $command = new self($delivery->getId());
        $command->name = $delivery->getName();
        $command->porog = $delivery->getPorog();
        $command->x1 = $delivery->getX1();
        $command->isPercent1 = $delivery->isPercent1();
        $command->x2 = $delivery->getX2();
        $command->isPercent2 = $delivery->isPercent2();
        $command->isTK = $delivery->isTK();
        $command->isOwnDelivery = $delivery->isOwnDelivery();
        $command->isMain = $delivery->isMain();
        return $command;
    }
}
