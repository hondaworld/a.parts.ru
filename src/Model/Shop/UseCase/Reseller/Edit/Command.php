<?php

namespace App\Model\Shop\UseCase\Reseller\Edit;

use App\Model\Shop\Entity\DeliveryTk\DeliveryTk;
use App\Model\Shop\Entity\Reseller\Reseller;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $resellerID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $resellerID)
    {
        $this->resellerID = $resellerID;
    }

    public static function fromEntity(Reseller $reseller): self
    {
        $command = new self($reseller->getId());
        $command->name = $reseller->getName();
        return $command;
    }
}
