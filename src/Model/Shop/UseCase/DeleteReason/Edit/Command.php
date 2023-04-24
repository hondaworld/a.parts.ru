<?php

namespace App\Model\Shop\UseCase\DeleteReason\Edit;

use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $deleteReasonID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $isMain;

    public function __construct(int $deleteReasonID)
    {
        $this->deleteReasonID = $deleteReasonID;
    }

    public static function fromEntity(DeleteReason $deleteReason): self
    {
        $command = new self($deleteReason->getId());
        $command->name = $deleteReason->getName();
        $command->isMain = $deleteReason->isMain();
        return $command;
    }
}
