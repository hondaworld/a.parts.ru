<?php

namespace App\Model\Provider\UseCase\Invoice\Edit;

use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerInvoiceID;

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

    public function __construct(int $providerInvoiceID)
    {
        $this->providerInvoiceID = $providerInvoiceID;
    }

    public static function fromEntity(ProviderInvoice $providerInvoice): self
    {
        $command = new self($providerInvoice->getId());
        $command->status_from = explode(',', $providerInvoice->getStatusFrom());
        $command->status_to = $providerInvoice->getStatusTo();
        $command->status_none = $providerInvoice->getStatusNone();
        $command->deleteReasonID = $providerInvoice->getDeleteReason()->getId();
        $command->price = $providerInvoice->getPrice();
        $command->price_email = $providerInvoice->getPriceEmail();
        $command->email_from = $providerInvoice->getEmailFrom();
        $command->priceadd = $providerInvoice->getPriceAdd();
        $command->num_number_type = $providerInvoice->getNum()->getNumberType();
        $command->num_number = $providerInvoice->getNum()->getNumber();
        $command->num_number_razd = $providerInvoice->getNum()->getNumberRazd();
        $command->num_price = $providerInvoice->getNum()->getPrice();
        $command->num_summ = $providerInvoice->getNum()->getSumm();
        $command->num_quantity = $providerInvoice->getNum()->getQuantity();
        $command->num_gtd = $providerInvoice->getNum()->getGtd();
        $command->num_country = $providerInvoice->getNum()->getCountry();

        return $command;
    }
}
