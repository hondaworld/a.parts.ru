<?php

namespace App\Model\Detail\UseCase\Dealer\Upload;

use App\Model\Card\Entity\Stock\ZapCardStock;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $numNumber = 0;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $numPrice = 1;

    /**
     * @var string
     */
    public $koef;

    /**
     * @var bool
     */
    public $isDelete;

    /**
     * @var string
     */
    public $file;
}
