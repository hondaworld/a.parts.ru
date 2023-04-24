<?php

namespace App\Model\Auto\UseCase\Auto\Edit;

use App\Model\Auto\Entity\Auto\Auto;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $autoID;
    /**
     * @Assert\NotBlank()
     */
    public $auto_modelID;

    public $vin;

    public $number;

    /**
     * @Assert\Length(
     *     max="4",
     *     min="4",
     *     maxMessage="Год должен быть 4 символа"
     * )
     */
    public $year;

    public function __construct(int $autoID)
    {
        $this->autoID = $autoID;
    }

    public static function fromEntity(Auto $auto): self
    {
        $command = new self($auto->getId());
        $command->auto_modelID = $auto->getModel() ? $auto->getModel()->getId() : 0;
        $command->vin = $auto->getVin()->getValue();
        $command->number = $auto->getNumber()->getValue();
        $command->year = $auto->getYear();
        return $command;
    }
}
