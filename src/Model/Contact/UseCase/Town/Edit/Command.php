<?php

namespace App\Model\Contact\UseCase\Town\Edit;

use App\Model\Contact\Entity\Town\Town;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $townID;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $name_doc;

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
    public $regionID;

    /**
     * @Assert\NotBlank()
     */
    public $typeID;

    public $isFree;

    public $country;

    public function __construct(int $townID)
    {
        $this->townID = $townID;
    }

    public static function fromTown(Town $town): self
    {
        $command = new self($town->getId());
        $command->name = $town->getName();
        $command->name_short = $town->getNameShort();
        $command->name_doc = $town->getNameDoc();
        $command->daysFromMoscow = $town->getDaysFromMoscow();
        $command->isFree = $town->getIsFree();
        $command->regionID = $town->getRegion()->getId();
        $command->typeID = $town->getType()->getId();
        $command->country = $town->getRegion()->getCountry();
        return $command;
    }
}
