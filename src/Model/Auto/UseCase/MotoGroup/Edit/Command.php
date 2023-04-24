<?php

namespace App\Model\Auto\UseCase\MotoGroup\Edit;

use App\Model\Auto\Entity\MotoGroup\MotoGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $moto_groupID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $photo;

    public function __construct(int $moto_groupID)
    {
        $this->moto_groupID = $moto_groupID;
    }

    public static function fromEntity(MotoGroup $motoGroup, string $photoDirectory): self
    {
        $command = new self($motoGroup->getId());
        $command->name = $motoGroup->getName();
        $command->photo = $motoGroup->getPhoto() ? $photoDirectory . $motoGroup->getPhoto() : '';
        return $command;
    }
}
