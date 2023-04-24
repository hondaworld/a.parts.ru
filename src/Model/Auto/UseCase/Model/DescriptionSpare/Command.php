<?php

namespace App\Model\Auto\UseCase\Model\DescriptionSpare;

use App\Model\Auto\Entity\Model\AutoModel;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_modelID;

    public $spare;

    public function __construct(int $auto_modelID)
    {
        $this->auto_modelID = $auto_modelID;
    }

    public static function fromEntity(AutoModel $autoModel): self
    {
        $command = new self($autoModel->getId());
        $command->spare = $autoModel->getDescription()->getSpare();
        return $command;
    }
}
