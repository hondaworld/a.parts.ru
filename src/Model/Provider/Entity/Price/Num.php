<?php


namespace App\Model\Provider\Entity\Price;

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
    private $creater;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $number;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $price;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $quantity;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $rg;

    /**
     * @var string
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $creater_add;

    public function __construct(?string $creater = '', ?string $number = '', ?string $price = '', ?string $quantity = '', ?string $name = '', ?string $rg = '', ?string $creater_add = '')
    {
        $this->creater = $creater !== null ? $creater : '';
        $this->number = $number !== null ? $number : '';
        $this->price = $price !== null ? $price : '';
        $this->quantity = $quantity !== null ? $quantity : '';
        $this->name = $name !== null ? $name : '';
        $this->rg = $rg !== null ? $rg : '';
        $this->creater_add = $creater_add !== null ? $creater_add : '';
    }

    /**
     * @return string
     */
    public function getCreater(): string
    {
        return $this->creater;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
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
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRg(): string
    {
        return $this->rg;
    }

    /**
     * @return string
     */
    public function getCreaterAdd(): string
    {
        return $this->creater_add;
    }

    public function getLabel(string $name): string
    {
        switch ($name)
        {
            case 'creater' : return 'Производитель';
            case 'number' : return 'Номер';
            case 'price' : return 'Цена';
            case 'quantity' : return 'Количество';
            case 'name' : return 'Наименование';
            case 'rg' : return 'RG';
            case 'creater_add' : return 'Britpart';
        }
        return '';
    }

    public function getLabelFromColNum(int $num): string
    {
        if ($this->getCreater() != '' && $this->getCreater() == $num) return $this->getLabel('creater');
        if ($this->getNumber() != '' && $this->getNumber() == $num) return $this->getLabel('number');
        if ($this->getPrice() != '' && $this->getPrice() == $num) return $this->getLabel('price');
        if ($this->getQuantity() != '' && $this->getQuantity() == $num) return $this->getLabel('quantity');
        if ($this->getName() != '' && $this->getName() == $num) return $this->getLabel('name');
        if ($this->getRg() != '' && $this->getRg() == $num) return $this->getLabel('rg');
        if ($this->getCreaterAdd() != '' && $this->getCreaterAdd() == $num) return $this->getLabel('creater_add');
        return '';
    }

    public function getNameFromColNum(int $num): string
    {
        if ($this->getCreater() != '' && $this->getCreater() == $num) return 'creater';
        if ($this->getNumber() != '' && $this->getNumber() == $num) return 'number';
        if ($this->getPrice() != '' && $this->getPrice() == $num) return 'price';
        if ($this->getQuantity() != '' && $this->getQuantity() == $num) return 'quantity';
        if ($this->getName() != '' && $this->getName() == $num) return 'name';
        if ($this->getRg() != '' && $this->getRg() == $num) return 'rg';
        if ($this->getCreaterAdd() != '' && $this->getCreaterAdd() == $num) return 'creater_add';
        return '';
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

    public function getMaxCol(): int
    {
        return max([
            intval($this->getCreater()),
            intval($this->getCreaterAdd()),
            intval($this->getName()),
            intval($this->getNumber()),
            intval($this->getPrice()),
            intval($this->getQuantity()),
            intval($this->getRg()),
        ]);
    }
}