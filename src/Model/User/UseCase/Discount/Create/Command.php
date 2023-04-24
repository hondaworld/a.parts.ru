<?php

namespace App\Model\User\UseCase\Discount\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $summ;

    /**
     * @Assert\NotBlank()
     */
    public $discount_spare;

    /**
     * @Assert\NotBlank()
     */
    public $discount_service;
}
