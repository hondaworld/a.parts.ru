<?php

namespace App\Model\Auto\UseCase\Moto\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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
}
