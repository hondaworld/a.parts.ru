<?php

namespace App\Model\Ticket\UseCase\ClientTicketGroup\Edit;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $groupID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $isHideUser;

    public $isClose;

    /**
     * @var array
     */
    public $managers;

    public function __construct(int $groupID)
    {
        $this->groupID = $groupID;
    }

    public static function fromEntity(ClientTicketGroup $clientTicketGroup): self
    {
        $command = new self($clientTicketGroup->getId());
        $command->name = $clientTicketGroup->getName();
        $command->isHideUser = $clientTicketGroup->isHideUser();
        $command->isClose = $clientTicketGroup->isClose();
        $command->managers = array_map(function (Manager $manager): int {
            return $manager->getId();
        }, $clientTicketGroup->getManagers());
        return $command;
    }
}
