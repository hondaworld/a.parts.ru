<?php

namespace App\Model\Order\UseCase\Good\DateOfDelivery;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofdelivery;

    public function __construct(?\DateTime $dateofdelivery)
    {
        $this->dateofdelivery = $dateofdelivery;
    }
}
