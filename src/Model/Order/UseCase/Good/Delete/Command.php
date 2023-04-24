<?php

namespace App\Model\Order\UseCase\Good\Delete;

use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank(
     *     message="Заполните причину удаления"
     * )
     */
    public $deleteReason;
}
