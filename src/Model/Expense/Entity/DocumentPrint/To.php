<?php


namespace App\Model\Expense\Entity\DocumentPrint;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class To
{
    /**
     * @ORM\Column(type="string", length=30)
     */
    private $okpo;

    /**
     * @ORM\Column(type="text")
     */
    private $nakladnaya;

    /**
     * @ORM\Column(type="text")
     */
    private $chek;


    public function __construct(string $okpo, string $nakladnaya, string $chek)
    {
        $this->okpo = $okpo;
        $this->nakladnaya = $nakladnaya;
        $this->chek = $chek;
    }

    /**
     * @return string
     */
    public function getOkpo(): string
    {
        return $this->okpo;
    }

    /**
     * @return string
     */
    public function getNakladnaya(): string
    {
        return $this->nakladnaya;
    }

    /**
     * @return string
     */
    public function getCheck(): string
    {
        return $this->chek;
    }

}