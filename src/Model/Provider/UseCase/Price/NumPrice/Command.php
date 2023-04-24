<?php

namespace App\Model\Provider\UseCase\Price\NumPrice;

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
     * @var string
     */
    public $file;
    public $isPrice = 1;

    public function __construct(int $providerPriceID)
    {
        $this->providerPriceID = $providerPriceID;
    }

    public static function fromEntity(ProviderPrice $providerPrice): self
    {
        $command = new self($providerPrice->getId());
        return $command;
    }
}
