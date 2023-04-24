<?php

namespace App\Model\Contact\UseCase\TownRegion\Create;

use App\Model\Contact\Entity\Country\Country;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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

    public $country;

    public function __construct(Country $country)
    {
        $this->country = $country;
    }
}
