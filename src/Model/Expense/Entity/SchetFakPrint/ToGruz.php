<?php


namespace App\Model\Expense\Entity\SchetFakPrint;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class ToGruz
{

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gruz_name;

    /**
     * @ORM\Column(type="text")
     */
    private $address_gruz;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $inn_gruz;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $kpp_gruz;


    public function __construct(string $gruz_name, string $address_gruz, string $inn_gruz = '', string $kpp_gruz = '')
    {
        $this->gruz_name = $gruz_name;
        $this->address_gruz = $address_gruz;
        $this->inn_gruz = $inn_gruz;
        $this->kpp_gruz = $kpp_gruz;
    }

    /**
     * @return string
     */
    public function getGruzName(): string
    {
        return $this->gruz_name;
    }

    /**
     * @return string
     */
    public function getAddressGruz(): string
    {
        return $this->address_gruz;
    }

    /**
     * @return string
     */
    public function getInnGruz(): string
    {
        return $this->inn_gruz;
    }

    /**
     * @return string
     */
    public function getKppGruz(): string
    {
        return $this->kpp_gruz;
    }


}