<?php

namespace App\Model\Sklad\UseCase\ZapSklad\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name_short;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $isTorg = true;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $koef = 1.00;

    public $optID;

    public $isMain;
}
