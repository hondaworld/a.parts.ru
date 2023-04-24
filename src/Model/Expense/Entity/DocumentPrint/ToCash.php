<?php


namespace App\Model\Expense\Entity\DocumentPrint;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class ToCash
{
    /**
     * @ORM\Column(type="string", length=30)
     */
    private $cash_okpo;

    /**
     * @ORM\Column(type="text")
     */
    private $cash;


    public function __construct(string $cash_okpo, string $cash)
    {
        $this->cash_okpo = $cash_okpo;
        $this->cash = $cash;
    }

    /**
     * @return string
     */
    public function getCashOkpo(): string
    {
        return $this->cash_okpo;
    }

    /**
     * @return string
     */
    public function getCash(): string
    {
        return $this->cash;
    }

}