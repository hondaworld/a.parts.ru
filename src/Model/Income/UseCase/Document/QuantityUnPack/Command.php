<?php

namespace App\Model\Income\UseCase\Document\QuantityUnPack;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Positive ()
     */
    public $quantityUnPack;
}
