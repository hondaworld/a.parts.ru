<?php

namespace App\Model\Order\UseCase\Good\ReserveFromMail;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public array $cols;

    public ZapSklad $zapSklad;

    public User $user;

    public function __construct(array $cols, User $user, ?ZapSklad $zapSklad)
    {
        $this->cols = $cols;
        $this->user = $user;
        $this->zapSklad = $zapSklad;
    }
}
