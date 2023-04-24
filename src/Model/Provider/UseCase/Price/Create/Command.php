<?php

namespace App\Model\Provider\UseCase\Price\Create;

use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Provider\Entity\Group\ProviderPriceGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerPriceGroupID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $currencyID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $description;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $srok;

    /**
     * @var int
     * @Assert\Positive (
     *     message="Значение должно быть положительным числом"
     * )
     * @Assert\NotBlank()
     */
    public $srokInDays;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $koef = 1;

    /**
     * @var string
     */
    public $currencyOwn;

    /**
     * @var string
     */
    public $deliveryForWeight;

    /**
     * @var int
     * @Assert\PositiveOrZero (
     *     message="Значение должно быть положительным числом"
     * )
     */
    public $deliveryInPercent;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $discount;

    /**
     * @var int
     * @Assert\PositiveOrZero (
     *     message="Значение должно быть положительным числом"
     * )
     * @Assert\LessThan(
     *     value = 1000,
     *     message="Значение должно быть меньше 1000"
     * )
     * @Assert\NotBlank()
     */
    public $daysofchanged;

    /**
     * @var boolean
     */
    public $clients_hide;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->providerPriceGroupID = ProviderPriceGroup::DEFAULT_ID;
        $this->currencyID = $currencyRepository->getCurrencyNational()->getId();
    }
}
