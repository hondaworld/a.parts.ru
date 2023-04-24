<?php

namespace App\Model\Contact\UseCase\Country\Edit;

use App\Model\Contact\Entity\Country\Country;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $countryID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $code;

    public function __construct(int $countryID)
    {
        $this->countryID = $countryID;
    }

    public static function fromCountry(Country $country): self
    {
        $command = new self($country->getId());
        $command->name = $country->getName();
        $command->code = $country->getCode();
        return $command;
    }
}
