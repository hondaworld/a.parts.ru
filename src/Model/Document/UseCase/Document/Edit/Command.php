<?php

namespace App\Model\Document\UseCase\Document\Edit;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Beznal\UseCase\Beznal\Bank;
use App\Model\Document\Entity\Document\Document;
use App\ReadModel\Beznal\BankFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $documentID;

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

    public function __construct(int $documentID) {
        $this->documentID = $documentID;
    }

    public static function fromDocument(Document $document): self
    {
        $command = new self($document->getId());
        $command->manager = $document->getManager();
        $command->user = $document->getUser();
        $command->firm = $document->getFirm();
        $command->doc_identID = $document->getIdentification()->getId();
        $command->serial = $document->getSerial();
        $command->number = $document->getNumber();
        $command->organization = $document->getOrganization();
        $command->dateofdoc = $document->getDateofdoc();
        $command->description = $document->getDescription();
        $command->isMain = $document->isMain();
        return $command;
    }
}
