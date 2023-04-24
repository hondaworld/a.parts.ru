<?php

namespace App\Model\Order\UseCase\Good\Perem;

use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, склад"
     * )
     */
    public $zapSkladID;

    public $cols;
}
