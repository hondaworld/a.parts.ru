<?php


namespace App\Model\Provider\Entity\ProviderInvoice;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Num
{
    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $number;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $number_type;

    /**
     * @var string
     * @ORM\Column(type="string", length=2, nullable=false)
     */
    private $number_razd;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $price;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $summ;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $quantity;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $gtd;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $country;

    public function __construct(?string $number, ?int $number_type, ?string $number_razd, ?string $price, ?string $summ, ?string $quantity, ?string $gtd, ?string $country)
    {
        $this->number = $number === null ? '' : $number;
        $this->number_type = $number_type ?: 0;
        $this->number_razd = $number_razd === null ? '' : $number_razd;
        $this->price = $price === null ? '' : $price;
        $this->summ = $summ === null ? '' : $summ;
        $this->quantity = $quantity === null ? '' : $quantity;
        $this->gtd = $gtd === null ? '' : $gtd;
        $this->country = $country === null ? '' : $country;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return int
     */
    public function getNumberType(): int
    {
        return $this->number_type;
    }

    /**
     * @return string
     */
    public function getNumberRazd(): string
    {
        return $this->number_razd;
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
    public function getSumm(): string
    {
        return $this->summ;
    }

    /**
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getGtd(): string
    {
        return $this->gtd;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    public static function assoc(): array
    {
        return [
            'creater' => 'Производитель',
            'number' => 'Номер',
            'price' => 'Цена',
            'quantity' => 'Количество',
            'name' => 'Наименование',
            'rg' => 'RG',
            'creater_add' => 'Britpart',
        ];
    }
}