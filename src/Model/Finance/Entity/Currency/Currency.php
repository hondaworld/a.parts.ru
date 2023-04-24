<?php

namespace App\Model\Finance\Entity\Currency;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Finance\Entity\CurrencyRate\CurrencyRate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CurrencyRepository::class)
 * @ORM\Table(name="currency")
 */
class Currency
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="currencyID")
     */
    private $currencyID;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $sex;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $int_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $int_1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $int_2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $int_5;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fract_name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fract_1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fract_2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fract_5;

    /**
     * @ORM\Column(type="boolean", name="isNational")
     */
    private $isNational = false;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $koef = 1;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=4)
     */
    private $fix_rate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_fix_rate = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var CurrencyRate[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Finance\Entity\CurrencyRate\CurrencyRate", mappedBy="currencyID_to")
     */
    private $rates_to;

    /**
     * @var CurrencyRate[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Finance\Entity\CurrencyRate\CurrencyRate", mappedBy="currencyID")
     */
    private $rates_from;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="currency")
     */
    private $zapCards;

    public function __construct(
        int $code,
        string $name_short,
        string $name,
        string $int_name,
        string $int_1,
        string $int_2,
        string $int_5,
        string $fract_name,
        string $fract_1,
        string $fract_2,
        string $fract_5,
        string $koef,
        ?string $fix_rate,
        bool $is_fix_rate,
        string $sex
    )
    {

        $this->code = $code;
        $this->name_short = $name_short;
        $this->name = $name;
        $this->int_name = $int_name;
        $this->int_1 = $int_1;
        $this->int_2 = $int_2;
        $this->int_5 = $int_5;
        $this->fract_name = $fract_name;
        $this->fract_1 = $fract_1;
        $this->fract_2 = $fract_2;
        $this->fract_5 = $fract_5;
        $this->koef = str_replace(',', '.', $koef);
        $this->fix_rate = $fix_rate ? str_replace(',', '.', $fix_rate) : 0;
        $this->is_fix_rate = $is_fix_rate;
        $this->sex = $sex;
    }

    public function update(
        int $code,
        string $name_short,
        string $name,
        string $int_name,
        string $int_1,
        string $int_2,
        string $int_5,
        string $fract_name,
        string $fract_1,
        string $fract_2,
        string $fract_5,
        string $koef,
        ?string $fix_rate,
        bool $is_fix_rate,
        string $sex
    ): void
    {
        $this->code = $code;
        $this->name_short = $name_short;
        $this->name = $name;
        $this->int_name = $int_name;
        $this->int_1 = $int_1;
        $this->int_2 = $int_2;
        $this->int_5 = $int_5;
        $this->fract_name = $fract_name;
        $this->fract_1 = $fract_1;
        $this->fract_2 = $fract_2;
        $this->fract_5 = $fract_5;
        $this->koef = str_replace(',', '.', $koef);
        $this->fix_rate = $fix_rate ? str_replace(',', '.', $fix_rate) : 0;
        $this->is_fix_rate = $is_fix_rate;
        $this->sex = $sex;
    }

    public function getId(): ?int
    {
        return $this->currencyID;
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function getIntName(): ?string
    {
        return $this->int_name;
    }

    public function getInt1(): ?string
    {
        return $this->int_1;
    }

    public function getInt2(): ?string
    {
        return $this->int_2;
    }

    public function getInt5(): ?string
    {
        return $this->int_5;
    }

    public function getFractName(): ?string
    {
        return $this->fract_name;
    }

    public function getFract1(): ?string
    {
        return $this->fract_1;
    }

    public function getFract2(): ?string
    {
        return $this->fract_2;
    }

    public function getFract5(): ?string
    {
        return $this->fract_5;
    }

    public function getIsNational(): ?bool
    {
        return $this->isNational;
    }

    public function getKoef(): ?string
    {
        return $this->koef;
    }

    public function getFixRate(): ?string
    {
        return $this->fix_rate;
    }

    public function getIsFixRate(): ?bool
    {
        return $this->is_fix_rate;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function getNoneDelete(): ?bool
    {
        return $this->noneDelete;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }
}
