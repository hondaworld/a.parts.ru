<?php

namespace App\Model\Auto\UseCase\MotoModel\DescriptionSpare;

use App\Model\Auto\Entity\MotoModel\MotoModel;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $moto_modelID;

    public $spare;

    public function __construct(int $moto_modelID)
    {
        $this->moto_modelID = $moto_modelID;
    }

    public static function fromEntity(MotoModel $motoModel): self
    {
        $command = new self($motoModel->getId());
        $command->spare = $motoModel->getDescription()->getSpare();
        return $command;
    }
}
