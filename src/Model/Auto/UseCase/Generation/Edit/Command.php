<?php

namespace App\Model\Auto\UseCase\Generation\Edit;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $auto_generationID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $name_rus;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="4",
     *     min="4",
     *     maxMessage="Год должен быть 4 символа"
     * )
     */
    public $yearfrom;

    /**
     * @Assert\Length(
     *     max="4",
     *     min="4",
     *     maxMessage="Год должен быть 4 символа"
     * )
     */
    public $yearto;

    public function __construct(int $auto_generationID)
    {
        $this->auto_generationID = $auto_generationID;
    }

    public static function fromEntity(AutoGeneration $autoGeneration): self
    {
        $command = new self($autoGeneration->getId());
        $command->auto_generationID = $autoGeneration->getId();
        $command->name = $autoGeneration->getName();
        $command->name_rus = $autoGeneration->getNameRus();
        $command->yearfrom = $autoGeneration->getYearfrom();
        $command->yearto = $autoGeneration->getYearto();
        return $command;
    }
}
