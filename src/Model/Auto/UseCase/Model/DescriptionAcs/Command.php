<?php

namespace App\Model\Auto\UseCase\Model\DescriptionAcs;

use App\Model\Auto\Entity\Model\AutoModel;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_modelID;

    public $acs;

    public function __construct(int $auto_modelID)
    {
        $this->auto_modelID = $auto_modelID;
    }

    public static function fromEntity(AutoModel $autoModel): self
    {
        $command = new self($autoModel->getId());
        $command->acs = $autoModel->getDescription()->getAcs();
        return $command;
    }
}
