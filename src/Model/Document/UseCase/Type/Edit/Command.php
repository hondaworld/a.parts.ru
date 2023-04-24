<?php

namespace App\Model\Document\UseCase\Type\Edit;

use App\Model\Document\Entity\Type\DocumentType;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $doc_typeID;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="5",
     *     minMessage="Короткое наименование должно быть не больше 5 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\Length(
     *     max="50",
     *     minMessage="Файл должен быть не больше 50 символов"
     * )
     */
    public $path;

    public function __construct(int $doc_typeID) {
        $this->doc_typeID = $doc_typeID;
    }

    public static function fromDocument(DocumentType $type): self
    {
        $command = new self($type->getId());
        $command->name_short = $type->getNameShort();
        $command->name = $type->getName();
        $command->path = $type->getPath();
        return $command;
    }
}
