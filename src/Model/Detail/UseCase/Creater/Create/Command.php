<?php

namespace App\Model\Detail\UseCase\Creater\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $name_rus;
    /**
     * @var boolean
     */
    public $isOriginal;

    /**
     * @var string
     */
    public $tableName = 'shopPriceN';

    /**
     * @var int
     */
    public $creater_weightID;

    /**
     * @var string
     */
    public $description;
}
