<?php

namespace App\Model\Order\UseCase\Kit\CreateOrder;

use App\Model\Provider\UseCase\Provider\User;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\Valid()
     */
    public $user;

    public $cols;

    public function __construct()
    {
        $this->user = new User();
    }
}
