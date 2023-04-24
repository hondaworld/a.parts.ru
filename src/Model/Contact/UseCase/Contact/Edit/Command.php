<?php

namespace App\Model\Contact\UseCase\Contact\Edit;

use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\UseCase\Contact\Address;
use App\Model\Contact\UseCase\Contact\Town;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $contactID;

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

    public function __construct(int $contactID) {
        $this->contactID = $contactID;
    }

    public static function fromContact(Contact $contact, TownFetcher $townFetcher): self
    {
        $command = new self($contact->getId());
        $command->manager = $contact->getManager();
        $command->user = $contact->getUser();
        $command->firm = $contact->getFirm();
        $command->address = new Address(
            new Town($contact->getTown()->getId(), $townFetcher->findTownsById($contact->getTown()->getId())->getTownFullName()),
            $contact->getAddress()->getZip(),
            $contact->getAddress()->getStreet(),
            $contact->getAddress()->getHouse(),
            $contact->getAddress()->getStr(),
            $contact->getAddress()->getKv()
        );
        $command->phonemob['phonemob'] = $contact->getPhonemob();
        $command->phone = $contact->getPhone();
        $command->fax = $contact->getFax();
        $command->http = $contact->getHttp();
        $command->email = $contact->getEmail();
        $command->description = $contact->getDescription();
        $command->isUr = $contact->getIsUr();
        $command->isMain = $contact->isMain();
        return $command;
    }
}
