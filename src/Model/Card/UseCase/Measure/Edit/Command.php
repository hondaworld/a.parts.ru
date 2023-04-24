<?php

namespace App\Model\Card\UseCase\Measure\Edit;

use App\Model\Card\Entity\Measure\EdIzm;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $ed_izmID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="10",
     *     minMessage="Краткое наименование должно не больше 10 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\Length(
     *     max="5",
     *     minMessage="Код ОКЕИ должно не больше 10 символов"
     * )
     */
    public $okei;

    public function __construct(int $ed_izmID)
    {
        $this->ed_izmID = $ed_izmID;
    }

    public static function fromEntity(EdIzm $edIzm): self
    {
        $command = new self($edIzm->getId());
        $command->name = $edIzm->getName();
        $command->name_short = $edIzm->getNameShort();
        $command->okei = $edIzm->getOkei();
        return $command;
    }
}
