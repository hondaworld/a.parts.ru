<?php


namespace App\Model\User\Entity\User;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class EmailPrice
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $email_price;

    /**
     * @ORM\Column(type="integer", name="email_price_zapSkladID")
     */
    private $email_price_zapSkladID;

    /**
     * @ORM\Column(type="boolean", name="isEmailPrice")
     */
    private $isEmailPrice;

    /**
     * @ORM\Column(type="boolean", name="isEmailPriceSummary")
     */
    private $isEmailPriceSummary;

    public function __construct(string $value, ?int $email_price_zapSkladID, bool $isEmailPrice, bool $isEmailPriceSummary)
    {
        $this->email_price = $value;
        $this->email_price_zapSkladID = $email_price_zapSkladID ?: 0;
        $this->isEmailPrice = $isEmailPrice;
        $this->isEmailPriceSummary = $isEmailPriceSummary;
    }

    public function getValue(): string
    {
        return $this->email_price;
    }

    public function isPrice(): bool
    {
        return $this->isEmailPrice;
    }

    public function isPriceSummary(): bool
    {
        return $this->isEmailPriceSummary;
    }

    public function isEqual(self $other): bool
    {
        return $this->getValue() === $other->getValue();
    }

    public function getZapSkladID()
    {
        return $this->email_price_zapSkladID;
    }

}