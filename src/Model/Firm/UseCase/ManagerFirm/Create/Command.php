<?php

namespace App\Model\Firm\UseCase\ManagerFirm\Create;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\OrgGroup\OrgGroup;
use App\Model\Firm\Entity\OrgJob\OrgJob;
use App\Model\Manager\Entity\Manager\Manager;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $managerID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $firmID;

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

    public function __construct(object $object, ?OrgGroup $orgGroup, ?OrgJob $orgJob)
    {
        if ($object instanceof Firm) {
            $this->firm = $object;
            $this->firmID = $object->getId();
        }

        if ($object instanceof Manager) {
            $this->manager = $object;
            $this->managerID = $object->getId();
        }

        if ($orgGroup) {
            $this->org_groupID = $orgGroup->getId();
        }

        if ($orgJob) {
            $this->org_jobID = $orgJob->getId();
        }
    }
}
