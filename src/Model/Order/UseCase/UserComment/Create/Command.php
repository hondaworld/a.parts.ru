<?php

namespace App\Model\Order\UseCase\UserComment\Create;

use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank(
     *     message="Пожалуйста, введите комментарий"
     * )
     */
    public $comment;
}
