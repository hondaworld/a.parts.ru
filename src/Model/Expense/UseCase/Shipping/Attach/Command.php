<?php

namespace App\Model\Expense\UseCase\Shipping\Attach;

use App\Model\Expense\Entity\Shipping\Shipping;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $shippingID;

    /**
     * @var string
     */
    public $nakladnaya;

    public function __construct(int $shippingID)
    {
        $this->shippingID = $shippingID;
    }

    public static function fromEntity(Shipping $shipping, string $attachDirectory): self
    {
        $command = new self($shipping->getId());
        $command->nakladnaya = $shipping->getNakladnaya() ? $attachDirectory . $shipping->getNakladnaya() : '';
        return $command;
    }
}
