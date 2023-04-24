<?php


namespace App\Model\Provider\Entity\Price;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Price
{
    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=false)
     */
    private $razd;

    /**
     * @var string
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    private $razd_decimal;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $price;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $price_copy;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $price_email;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $email_from;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isNotCheckExt")
     */
    private $isNotCheckExt;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isUpdate")
     */
    private $isUpdate;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $rg_value;

    /**
     * @ORM\Column(type="koef", precision=7, scale=4)
     */
    private $priceadd;

    public function __construct(
        ?string $razd = '',
        ?string $razd_decimal = '',
        ?string $price = '',
        ?string $price_copy = '',
        ?string $price_email = '',
        ?string $email_from = '',
        bool $isNotCheckExt = false,
        bool $isUpdate = false,
        ?string $rg_value = '',
        ?string $priceadd = null
    )
    {
        $this->razd = $razd ?: '';
        $this->razd_decimal = $razd_decimal ?: '';
        $this->price = $price ?: '';
        $this->price_copy = $price_copy ?: '';
        $this->price_email = $price_email ?: '';
        $this->email_from = $email_from ?: '';
        $this->isNotCheckExt = $isNotCheckExt;
        $this->isUpdate = $isUpdate;
        $this->rg_value = $rg_value ?: '';
        $this->priceadd = $priceadd;
    }

    /**
     * @return string
     */
    public function getRazd(): string
    {
        return $this->razd;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPriceCopy(): string
    {
        return $this->price_copy;
    }

    /**
     * @return string
     */
    public function getPriceEmail(): string
    {
        return $this->price_email;
    }

    /**
     * @return string
     */
    public function getEmailFrom(): string
    {
        return $this->email_from;
    }

    /**
     * @return bool
     */
    public function isNotCheckExt(): ?bool
    {
        return $this->isNotCheckExt;
    }

    /**
     * @return bool
     */
    public function isUpdate(): ?bool
    {
        return $this->isUpdate;
    }

    /**
     * @return string
     */
    public function getRazdDecimal(): string
    {
        return $this->razd_decimal;
    }

    /**
     * @return string
     */
    public function getRgValue(): string
    {
        return $this->rg_value;
    }

    /**
     * @return string|null
     */
    public function getPriceadd(): ?string
    {
        return $this->priceadd;
    }

    public function getRazdForUpload()
    {
        return $this->razd == "tab" ? "\t" : $this->razd;
    }

}