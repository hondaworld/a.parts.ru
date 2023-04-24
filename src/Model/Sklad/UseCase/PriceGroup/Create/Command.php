<?php

namespace App\Model\Sklad\UseCase\PriceGroup\Create;

use App\Model\Provider\UseCase\Provider\User;
use App\Model\Sklad\Entity\PriceList\PriceList;
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
     * @var boolean
     */
    public $isMain;

    public $priceList;

    public function __construct(PriceList $priceList)
    {
        $this->priceList = $priceList;
    }
}
