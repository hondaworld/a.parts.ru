<?php

namespace App\Model\Order\UseCase\Site\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $url;

    public $isSklad;

    /**
     * @var string
     */
    public $norma_price;

    /**
     * @var array
     */
    public $creaters;

    /**
     * @var array
     */
    public $auto_marka;
}
