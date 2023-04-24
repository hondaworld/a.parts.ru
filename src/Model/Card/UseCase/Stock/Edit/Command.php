<?php

namespace App\Model\Card\UseCase\Stock\Edit;

use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Provider\Entity\Provider\Provider;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $stockID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $text;

    /**
     * @var array
     */
    public $providers;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public function __construct(int $stockID)
    {
        $this->stockID = $stockID;
    }

    public static function fromEntity(ZapCardStock $stock): self
    {
        $command = new self($stock->getId());
        $command->name = $stock->getName();
        $command->text = $stock->getText();
        $command->dateofadded = $stock->getDateofadded();
        $command->providers = array_map(function (Provider $provider): int {
            return $provider->getId();
        }, $stock->getProviders());
        return $command;
    }
}
