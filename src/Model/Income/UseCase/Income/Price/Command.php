<?php

namespace App\Model\Income\UseCase\Income\Price;

use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $incomeID;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     * @Assert\Positive (
     *     message="Значение должно быть больше нуля"
     * )
     */
    public $priceZak;

    public $priceDost;

    public $price;

    public function __construct(int $incomeID)
    {
        $this->incomeID = $incomeID;
    }

    public static function fromEntity(Income $income): self
    {
        $command = new self($income->getId());
        $command->priceZak = $income->getPriceZak();
        $command->priceDost = $income->getPriceDost();
        $command->price = $income->getPrice();
        return $command;
    }
}
