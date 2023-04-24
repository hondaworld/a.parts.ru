<?php

namespace App\Model\Document\UseCase\Document\Create;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $doc_identID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $serial;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofdoc;

    public $organization;

    public $description;

    public $isMain;

    public $manager;

    public $user;

    public $firm;

    public function __construct(object $object) {
        if ($object instanceof Manager) {
            $this->manager = $object;
        }

        if ($object instanceof User) {
            $this->user = $object;
        }

        if ($object instanceof Firm) {
            $this->firm = $object;
        }
    }
}
