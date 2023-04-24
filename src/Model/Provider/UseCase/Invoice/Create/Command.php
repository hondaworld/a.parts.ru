<?php

namespace App\Model\Provider\UseCase\Invoice\Create;

use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Provider\Entity\Group\ProviderPriceGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var array
     * @Assert\NotBlank()
     */
    public $status_from;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $status_to;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $status_none;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $deleteReasonID;

    /**
     * @var string
     */
    public $price;

    /**
     * @var string
     */
    public $price_email;

    /**
     * @var string
     */
    public $email_from;

    /**
     * @var int
     */
    public $num_number;

    /**
     * @var int
     * @Assert\Choice({0, 1, 2})
     * @Assert\NotBlank()
     */
    public $num_number_type;

    /**
     * @var int
     */
    public $num_number_razd;

    /**
     * @var string
     */
    public $priceadd;

    /**
     * @var int
     */
    public $num_price;

    /**
     * @var int
     */
    public $num_summ;

    /**
     * @var int
     */
    public $num_quantity;

    /**
     * @var int
     */
    public $num_gtd;

    /**
     * @var int
     */
    public $num_country;
}
