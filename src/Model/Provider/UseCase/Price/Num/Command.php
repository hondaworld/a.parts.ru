<?php

namespace App\Model\Provider\UseCase\Price\Num;

use App\Model\Provider\Entity\Price\ProviderPrice;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $providerPriceID;

    /**
     * @var array
     */
    public $fields;

    /**
     * @var int
     */
    public $maxCols = 3;

    /**
     * @var int
     */
    public $isCols = 0;

    /**
     * @var string
     */
    public $price;

    public function __construct(int $providerPriceID)
    {
        $this->providerPriceID = $providerPriceID;
    }

    public static function fromEntity(ProviderPrice $providerPrice, int $maxCol, ?string $price): self
    {
        $command = new self($providerPrice->getId());
        for ($i = 0; $i <= $maxCol; $i++) {
            $command->fields[$i] = $providerPrice->getNum()->getNameFromColNum($i);
        }
        $command->price = $price;
        return $command;
    }

    public function getField(int $num)
    {
        return 'field_' . $num;
    }

    public function __get($name)
    {
        $arr = explode('_', $name);
        $field = $arr[0];
        $num = $arr[1] ?: 0;
        if (isset($this->fields[$num]))
            return $this->fields[$num];
        else
            return null;
    }

    public function __set($name, $value)
    {
        $arr = explode('_', $name);
        $field = $arr[0];
        $num = $arr[1] ?: 0;
        $this->fields[$num] = $value;
    }
}
