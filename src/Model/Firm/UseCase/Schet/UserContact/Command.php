<?php

namespace App\Model\Firm\UseCase\Schet\UserContact;

use App\Model\Firm\Entity\Schet\Schet;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $schetID;

    /**
     * @Assert\NotBlank()
     */
    public $exp_user_contactID;

    public $exp_user;

    public function __construct(int $schetID)
    {
        $this->schetID = $schetID;
    }

    public static function fromEntity(Schet $schet): self
    {
        $command = new self($schet->getId());
        $command->exp_user_contactID = $schet->getExpUserContact()->getId();
        $command->exp_user = $schet->getExpUser();
        return $command;
    }
}
