<?php

namespace App\Model\Card\UseCase\Group\Edit;

use App\Model\Card\Entity\Group\ZapGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapGroupID;

    /**
     * @Assert\NotBlank()
     */
    public $zapCategoryID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $zapGroupID)
    {
        $this->zapGroupID = $zapGroupID;
    }

    public static function fromEntity(ZapGroup $zapGroup): self
    {
        $command = new self($zapGroup->getId());
        $command->zapCategoryID = $zapGroup->getZapCategory()->getId();
        $command->name = $zapGroup->getName();
        return $command;
    }
}
