<?php

namespace App\Model\Finance\Entity\CurrencyRate;

use App\Model\Finance\Entity\Currency\Currency;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CurrencyRateRepository::class)
 * @ORM\Table(name="currencyRate")
 */
class CurrencyRate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="currencyRateID")
     */
    private $currencyRateID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\Currency\Currency", inversedBy="rates_from")
     * @ORM\JoinColumn(name="currencyID", referencedColumnName="currencyID")
     */
    private $currencyID;

    /**
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\Currency\Currency", inversedBy="rates_to")
     * @ORM\JoinColumn(name="currencyID_to", referencedColumnName="currencyID")
     */
    private $currencyID_to;

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="integer")
     */
    private $numbers;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=4)
     */
    private $rate;

    public function __construct(
        Currency $currency_to,
        Currency $currency,
        string $rate,
        \datetime $dateofadded,
        int $numbers
    )
    {
        $this->currencyID_to = $currency_to;
        $this->currencyID = $currency;
        $this->numbers = $numbers;
        $this->dateofadded = $dateofadded;
        $this->rate = str_replace(',', '.', $rate);
    }

    public function update(
        Currency $currency_to,
        Currency $currency,
        string $rate,
        \datetime $dateofadded,
        int $numbers
    )
    {
        $this->currencyID_to = $currency_to;
        $this->currencyID = $currency;
        $this->numbers = $numbers;
        $this->dateofadded = $dateofadded;
        $this->rate = str_replace(',', '.', $rate);
    }

    public function getCurrencyRateID(): ?int
    {
        return $this->currencyRateID;
    }

    public function getCurrencyID(): Currency
    {
        return $this->currencyID;
    }

    public function getCurrencyIDTo(): Currency
    {
        return $this->currencyID_to;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getNumbers(): ?int
    {
        return $this->numbers;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }
}
