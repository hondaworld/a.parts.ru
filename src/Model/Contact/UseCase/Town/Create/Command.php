<?php

namespace App\Model\Contact\UseCase\Town\Create;

use App\Model\Contact\Entity\Country\Country;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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

    public function __construct(Country $country)
    {
        $this->country = $country;
    }
}
