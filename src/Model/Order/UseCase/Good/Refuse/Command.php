<?php

namespace App\Model\Order\UseCase\Good\Refuse;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, причину отказа"
     * )
     */
    public $deleteReasonID;

    public $cols;
}
