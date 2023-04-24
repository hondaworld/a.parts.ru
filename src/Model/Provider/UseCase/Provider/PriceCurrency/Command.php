<?php

namespace App\Model\Provider\UseCase\Provider\PriceCurrency;

use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Provider\Entity\Provider\Provider;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerID;


    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     * @Assert\NotBlank()
     */
    public $koef = 1;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $currencyOwn;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $currencyID;

    public function __construct(int $providerID)
    {
        $this->providerID = $providerID;
    }

    public static function fromEntity(Provider $provider, CurrencyRepository $currencyRepository): self
    {
        $command = new self($provider->getId());
        $command->currencyID = $currencyRepository->getCurrencyNational()->getId();
        return $command;
    }
}
