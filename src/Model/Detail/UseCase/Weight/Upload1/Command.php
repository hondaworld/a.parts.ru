<?php

namespace App\Model\Detail\UseCase\Weight\Upload1;

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
     * @var string
     */
    public $file;
}
