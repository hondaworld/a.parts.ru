<?php

namespace App\Model\Shop\UseCase\DeliveryTk\Edit;

use App\Model\Shop\Entity\DeliveryTk\DeliveryTk;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $delivery_tkID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $http;

    public $sms_text;

    public function __construct(int $delivery_tkID)
    {
        $this->delivery_tkID = $delivery_tkID;
    }

    public static function fromEntity(DeliveryTk $deliveryTk): self
    {
        $command = new self($deliveryTk->getId());
        $command->name = $deliveryTk->getName();
        $command->http = $deliveryTk->getHttp();
        $command->sms_text = $deliveryTk->getSmsText();
        return $command;
    }
}
