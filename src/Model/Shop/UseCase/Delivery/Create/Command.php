<?php

namespace App\Model\Shop\UseCase\Delivery\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $porog;

    public $x1;

    public $isPercent1;

    public $x2;

    public $isPercent2;

    public $isTK;

    public $isOwnDelivery;

    public $path;

    public $isMain;
}
