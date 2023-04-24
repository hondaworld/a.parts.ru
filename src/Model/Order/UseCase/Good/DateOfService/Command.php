<?php

namespace App\Model\Order\UseCase\Good\DateOfService;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofservice;

    public function __construct(?\DateTime $dateofservice)
    {
        $this->dateofservice = $dateofservice;
    }
}
