<?php

namespace App\Model\Auto\UseCase\Model\Edit;

use App\Model\Auto\Entity\Model\AutoModel;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_modelID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $name_rus;

    public $path;

    public function __construct(int $auto_modelID)
    {
        $this->auto_modelID = $auto_modelID;
    }

    public static function fromEntity(AutoModel $autoModel): self
    {
        $command = new self($autoModel->getId());
        $command->auto_modelID = $autoModel->getId();
        $command->name = $autoModel->getName();
        $command->name_rus = $autoModel->getNameRus();
        $command->path = $autoModel->getPath();
        return $command;
    }
}
