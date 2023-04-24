<?php

namespace App\Model\Card\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MainRepository::class)
 * @ORM\Table(name="main")
 */
class Main
{
    public const DEFAULT_ID = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="mainID")
     */
    private $mainID;

    /**
     * @ORM\Column(type="integer")
     */
    private $rounding;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $discount;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2, name="discountPartsOnline")
     */
    private $discountPartsOnline;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2, name="discountServiceOnline")
     */
    private $discountServiceOnline;

    /**
     * @ORM\Column(type="boolean", name="is_inPath")
     */
    private $is_inPath;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2, name="discountPartsOnlineCard")
     */
    private $discountPartsOnlineCard;

    /**
     * @ORM\Column(type="text")
     */
    private $header_text;

    /**
     * @ORM\Column(type="integer", name="daysIncomeSklad")
     */
    private $daysIncomeSklad;

    /**
     * @ORM\Column(type="integer", name="serviceNorma")
     */
    private $serviceNorma;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $sms_ru_balance;

    public function updateSmsRuBalance(string $balance)
    {
        $this->sms_ru_balance = $balance;
    }

    public function getId(): ?int
    {
        return $this->mainID;
    }

    public function getRounding(): ?int
    {
        return $this->rounding;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function getDiscountPartsOnline(): ?string
    {
        return $this->discountPartsOnline;
    }

    public function getDiscountServiceOnline(): ?string
    {
        return $this->discountServiceOnline;
    }

    public function getIsInPath(): ?bool
    {
        return $this->is_inPath;
    }

    public function getDiscountPartsOnlineCard(): ?string
    {
        return $this->discountPartsOnlineCard;
    }

    public function getHeaderText(): ?string
    {
        return $this->header_text;
    }

    public function getDaysIncomeSklad(): ?int
    {
        return $this->daysIncomeSklad;
    }

    public function getServiceNorma(): ?int
    {
        return $this->serviceNorma;
    }

    public function getSmsRuBalance(): ?string
    {
        return $this->sms_ru_balance;
    }
}
