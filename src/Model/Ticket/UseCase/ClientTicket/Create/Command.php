<?php

namespace App\Model\Ticket\UseCase\ClientTicket\Create;

use App\Model\Manager\Entity\Manager\Manager;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $text;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $groupID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $user_subject;

    /**
     * @var string
     */
    public $attach;

    public $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }
}
