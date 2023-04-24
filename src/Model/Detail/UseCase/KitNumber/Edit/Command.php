<?php

namespace App\Model\Detail\UseCase\KitNumber\Edit;

use App\Model\Detail\Entity\KitNumber\ZapCardKitNumber;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $quantity;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromEntity(ZapCardKitNumber $zapCardKitNumber): self
    {
        $command = new self($zapCardKitNumber->getId());
        $command->number = $zapCardKitNumber->getNumber()->getValue();
        $command->quantity = $zapCardKitNumber->getQuantity();
        return $command;
    }
}
