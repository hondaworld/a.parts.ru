<?php

namespace App\Model\Sklad\UseCase\Parts\QuantityMin;

use App\Model\Card\Entity\Location\ZapSkladLocation;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapSkladLocationID;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     */
    public $quantityMin;

    public function __construct(int $zapSkladLocationID)
    {
        $this->zapSkladLocationID = $zapSkladLocationID;
    }

    public static function fromEntity(ZapSkladLocation $zapSkladLocation): self
    {
        $command = new self($zapSkladLocation->getId());
        $command->quantityMin = $zapSkladLocation->getQuantityMin();
        return $command;
    }
}
