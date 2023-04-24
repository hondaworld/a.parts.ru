<?php

namespace App\Model\Firm\UseCase\ManagerFirm\Edit;

use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $linkID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $org_groupID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $org_jobID;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofclosed;

    public $firm;

    public $manager;

    public function __construct(int $linkID) {
        $this->linkID = $linkID;
    }

    public static function fromEntity(ManagerFirm $managerFirm): self
    {
        $command = new self($managerFirm->getId());
        $command->firm = $managerFirm->getFirm();
        $command->manager = $managerFirm->getManager();
        $command->org_groupID = $managerFirm->getOrgGroup()->getId();
        $command->org_jobID = $managerFirm->getOrgJob()->getId();
        $command->dateofadded = $managerFirm->getDateofadded();
        $command->dateofclosed = $managerFirm->getDateofclosed();
        return $command;
    }
}
