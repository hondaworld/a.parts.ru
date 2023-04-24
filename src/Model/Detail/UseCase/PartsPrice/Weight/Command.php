<?php

namespace App\Model\Detail\UseCase\PartsPrice\Weight;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @Assert\NotBlank(
     *     message="Вес должен быть заполнен"
     * )
     */
    public $weight;

    public $weightIsReal;
}
