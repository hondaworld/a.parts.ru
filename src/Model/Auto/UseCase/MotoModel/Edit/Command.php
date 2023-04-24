<?php

namespace App\Model\Auto\UseCase\MotoModel\Edit;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $moto_modelID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $moto_groupID;

    public function __construct(int $moto_modelID)
    {
        $this->moto_modelID = $moto_modelID;
    }

    public static function fromEntity(MotoModel $motoModel): self
    {
        $command = new self($motoModel->getId());
        $command->name = $motoModel->getName();
        $command->moto_groupID = $motoModel->getGroup()->getId();
        return $command;
    }
}
