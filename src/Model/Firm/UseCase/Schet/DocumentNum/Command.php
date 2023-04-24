<?php

namespace App\Model\Firm\UseCase\Schet\DocumentNum;

use App\Model\Firm\Entity\Schet\Schet;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $schetID;

    /**
     * @Assert\Length(
     *     max="15",
     *     minMessage="Префикс должен быть не больше 15 символов"
     * )
     */
    public $document_prefix;

    /**
     * @Assert\Length(
     *     max="15",
     *     minMessage="Суфикс должен быть не больше 15 символов"
     * )
     */
    public $document_sufix;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public $comment;

    public function __construct(int $schetID)
    {
        $this->schetID = $schetID;
    }

    public static function fromEntity(Schet $schet): self
    {
        $command = new self($schet->getId());
        $command->document_prefix = $schet->getDocument()->getDocumentPrefix();
        $command->document_sufix = $schet->getDocument()->getDocumentSufix();
        $command->dateofadded = $schet->getDateofadded();
        $command->comment = $schet->getComment();
        return $command;
    }
}
