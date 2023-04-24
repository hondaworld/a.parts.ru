<?php

namespace App\Model\Document\UseCase\Identification\Edit;

use App\Model\Document\Entity\Identification\DocumentIdentification;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $doc_identID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $doc_identID) {
        $this->doc_identID = $doc_identID;
    }

    public static function fromDocument(DocumentIdentification $identification): self
    {
        $command = new self($identification->getId());
        $command->name = $identification->getName();
        return $command;
    }
}
