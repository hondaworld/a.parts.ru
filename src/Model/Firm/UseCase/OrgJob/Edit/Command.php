<?php

namespace App\Model\Firm\UseCase\OrgJob\Edit;

use App\Model\Firm\Entity\OrgJob\OrgJob;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $org_jobID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $isMain;

    public function __construct(int $org_jobID) {
        $this->org_jobID = $org_jobID;
    }

    public static function fromEntity(OrgJob $orgJob): self
    {
        $command = new self($orgJob->getId());
        $command->name = $orgJob->getName();
        $command->isMain = $orgJob->isMain();
        return $command;
    }
}
