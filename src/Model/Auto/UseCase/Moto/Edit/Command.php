<?php

namespace App\Model\Auto\UseCase\Moto\Edit;

use App\Model\Auto\Entity\Moto\Moto;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $motoID;
    /**
     * @Assert\NotBlank()
     */
    public $moto_modelID;

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

    public function __construct(int $motoID)
    {
        $this->motoID = $motoID;
    }

    public static function fromEntity(Moto $moto): self
    {
        $command = new self($moto->getId());
        $command->moto_modelID = $moto->getModel()->getId();
        $command->vin = $moto->getVin()->getValue();
        $command->number = $moto->getNumber()->getValue();
        $command->year = $moto->getYear();
        return $command;
    }
}
