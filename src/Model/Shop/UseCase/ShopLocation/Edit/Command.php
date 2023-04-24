<?php

namespace App\Model\Shop\UseCase\ShopLocation\Edit;

use App\Model\Shop\Entity\Location\ShopLocation;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $locationID;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="25",
     *     exactMessage="Короткое наименование должно быть не больше 25 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(int $locationID) {
        $this->locationID = $locationID;
    }

    public static function fromDocument(ShopLocation $shopLocation): self
    {
        $command = new self($shopLocation->getId());
        $command->name = $shopLocation->getName();
        $command->name_short = $shopLocation->getNameShort();
        return $command;
    }
}
