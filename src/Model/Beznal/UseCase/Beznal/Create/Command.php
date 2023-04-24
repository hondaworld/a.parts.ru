<?php

namespace App\Model\Beznal\UseCase\Beznal\Create;

use App\Model\Beznal\UseCase\Beznal\Bank;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\Valid()
     */
    public $bank;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $rasschet;

    public $description;

    public $isMain;

    public $manager;

    public $user;

    public $firm;

    public function __construct(object $object)
    {
        if ($object instanceof Manager) {
            $this->manager = $object;
        }

        if ($object instanceof User) {
            $this->user = $object;
        }

        if ($object instanceof Firm) {
            $this->firm = $object;
        }

        $this->bank = new Bank();
    }
}
