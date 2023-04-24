<?php

namespace App\Model\Work\UseCase\Group\Edit;

use App\Model\Work\Entity\Group\WorkGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $workGroupID;

    /**
     * @Assert\NotBlank()
     */
    public $workCategoryID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $norma;

    public $isTO;

    public $sort;

    public function __construct(int $workGroupID)
    {
        $this->workGroupID = $workGroupID;
    }

    public static function fromEntity(WorkGroup $workGroup): self
    {
        $command = new self($workGroup->getId());
        $command->workCategoryID = $workGroup->getCategory()->getId();
        $command->name = $workGroup->getName();
        $command->norma = $workGroup->getNorma();
        $command->isTO = $workGroup->getIsTO();
        $command->sort = $workGroup->getSort();
        return $command;
    }
}
