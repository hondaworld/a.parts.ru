<?php

namespace App\Model\Contact\UseCase\Contact\Create;

use App\Model\Contact\UseCase\Contact\Address;
use App\Model\Contact\UseCase\Contact\Town;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\Valid()
     */
    public $address;

    public $phonemob;

    public $phone;

    public $fax;

    public $http;

    /**
     * @var string
     * @Assert\Email()
     */
    public $email;

    public $description;

    public $isUr;

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

        $this->address = new Address(new Town());
    }
}
