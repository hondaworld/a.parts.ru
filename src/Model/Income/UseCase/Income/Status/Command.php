<?php

namespace App\Model\Income\UseCase\Income\Status;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Выберите, пожалуйста, статус"
     * )
     */
    public $status;

    /**
     * @var \DateTime
     */
    public $dateofinplan;

    public $deleteReasonID;

    public $cols;
}
