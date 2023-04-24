<?php

namespace App\Model\Contact\UseCase\TownRegion\Edit;

use App\Model\Contact\Entity\TownRegion\TownRegion;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $regionID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     */
    public $daysFromMoscow;

    /**
     * @Assert\NotBlank()
     */
    public $countryID;

    public $country;

    public function __construct(int $regionID)
    {
        $this->regionID = $regionID;
    }

    public static function fromTownRegion(TownRegion $region): self
    {
        $command = new self($region->getId());
        $command->name = $region->getName();
        $command->daysFromMoscow = $region->getDaysFromMoscow();
        $command->countryID = $region->getCountry()->getId();
        $command->country = $region->getCountry();
        return $command;
    }
}
