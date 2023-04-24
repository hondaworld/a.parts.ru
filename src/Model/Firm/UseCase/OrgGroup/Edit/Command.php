<?php

namespace App\Model\Firm\UseCase\OrgGroup\Edit;

use App\Model\Firm\Entity\OrgGroup\OrgGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $org_groupID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $isMain;

    public function __construct(int $org_groupID) {
        $this->org_groupID = $org_groupID;
    }

    public static function fromEntity(OrgGroup $orgGroup): self
    {
        $command = new self($orgGroup->getId());
        $command->name = $orgGroup->getName();
        $command->isMain = $orgGroup->isMain();
        return $command;
    }
}
