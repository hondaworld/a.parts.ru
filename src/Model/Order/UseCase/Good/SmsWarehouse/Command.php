<?php

namespace App\Model\Order\UseCase\Good\SmsWarehouse;

use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, шаблон"
     * )
     */
    public $templateID;
}
