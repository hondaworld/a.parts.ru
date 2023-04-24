<?php

namespace App\Model\Beznal\UseCase\Bank\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $bik;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $korschet;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $address;

    /**
     * @var string
     */
    public $description;
}
