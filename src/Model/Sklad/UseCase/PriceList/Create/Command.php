<?php

namespace App\Model\Sklad\UseCase\PriceList\Create;

use App\Model\Provider\UseCase\Provider\User;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $koef_dealer;

    /**
     * @var boolean
     */
    public $no_discount;

    /**
     * @var boolean
     */
    public $isMain;
}
