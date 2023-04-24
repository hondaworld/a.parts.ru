<?php

namespace App\Model\Provider\UseCase\Price\Price;

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
     */
    public $superProviderPriceID;

    /**
     * @var int
     */
    public $createrID;

    /**
     * @var string
     */
    public $razd;

    /**
     * @var string
     */
    public $razd_decimal;

    /**
     * @var string
     */
    public $price;

    /**
     * @var string
     */
    public $price_copy;

    /**
     * @var string
     */
    public $price_email;

    /**
     * @var string
     */
    public $email_from;

    /**
     * @var string
     */
    public $rg_value;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $priceadd;

    /**
     * @var boolean
     */
    public $isNotCheckExt;

    /**
     * @var boolean
     */
    public $isUpdate;

    public function __construct(int $providerPriceID)
    {
        $this->providerPriceID = $providerPriceID;
    }

    public static function fromEntity(ProviderPrice $providerPrice): self
    {
        $command = new self($providerPrice->getId());
        $command->superProviderPriceID = $providerPrice->getSuperProviderPrice() ? $providerPrice->getSuperProviderPrice()->getId() : 0;
        $command->createrID = $providerPrice->getCreater() ? $providerPrice->getCreater()->getId() : 0;
        $command->razd = $providerPrice->getPrice()->getRazd();
        $command->razd_decimal = $providerPrice->getPrice()->getRazdDecimal();
        $command->price = $providerPrice->getPrice()->getPrice();
        $command->price_copy = $providerPrice->getPrice()->getPriceCopy();
        $command->price_email = $providerPrice->getPrice()->getPriceEmail();
        $command->email_from = $providerPrice->getPrice()->getEmailFrom();
        $command->rg_value = $providerPrice->getPrice()->getRgValue();
        $command->priceadd = $providerPrice->getPrice()->getPriceadd();
        $command->isNotCheckExt = $providerPrice->getPrice()->isNotCheckExt();
        $command->isUpdate = $providerPrice->getPrice()->isUpdate();

        return $command;
    }
}
