<?php

namespace App\Model\Provider\UseCase\Price\Edit;

use App\Model\Provider\Entity\Price\ProviderPrice;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerPriceID;

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

    public function __construct(int $providerPriceID)
    {
        $this->providerPriceID = $providerPriceID;
    }

    public static function fromEntity(ProviderPrice $providerPrice): self
    {
        $command = new self($providerPrice->getId());
        $command->name = $providerPrice->getName();
        $command->description = $providerPrice->getDescription();
        $command->providerPriceGroupID = $providerPrice->getGroup()->getId();
        $command->providerID = $providerPrice->getProvider()->getId();
        $command->currencyID = $providerPrice->getCurrency()->getId();
        $command->srok = $providerPrice->getSrok();
        $command->srokInDays = $providerPrice->getSrokInDays();
        $command->koef = $providerPrice->getKoef();
        $command->currencyOwn = $providerPrice->getCurrencyOwn();
        $command->deliveryForWeight = $providerPrice->getDeliveryForWeight();
        $command->deliveryInPercent = $providerPrice->getDeliveryInPercent();
        $command->discount = $providerPrice->getDiscount();
        $command->daysofchanged = $providerPrice->getDaysofchanged();
        $command->clients_hide = $providerPrice->isClientsHide();

        return $command;
    }
}
