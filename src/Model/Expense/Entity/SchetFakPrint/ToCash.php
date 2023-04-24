<?php


namespace App\Model\Expense\Entity\SchetFakPrint;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class ToCash
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cash_name;

    /**
     * @ORM\Column(type="text")
     */
    private $address_cash;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $inn_cash;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $kpp_cash;


    public function __construct(string $cash_name, string $address_cash, string $inn_cash = '', string $kpp_cash = '')
    {
        $this->cash_name = $cash_name;
        $this->address_cash = $address_cash;
        $this->inn_cash = $inn_cash;
        $this->kpp_cash = $kpp_cash;
    }

    /**
     * @return string
     */
    public function getCashName(): string
    {
        return $this->cash_name;
    }

    /**
     * @return string
     */
    public function getAddressCash(): string
    {
        return $this->address_cash;
    }

    /**
     * @return string
     */
    public function getInnCash(): string
    {
        return $this->inn_cash;
    }

    /**
     * @return string
     */
    public function getKppCash(): string
    {
        return $this->kpp_cash;
    }

}