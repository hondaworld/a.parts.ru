<?php

namespace App\Model\User\UseCase\User\Create;

use App\Model\User\Entity\Opt\Opt;
use App\Model\User\UseCase\User\Phonemob;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $middlename;

    /**
     * @var Phonemob
     * @Assert\Valid()
     */
    public $phonemob;

    /**
     * @var Town
     * @Assert\Valid()
     */
    public $town;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $optID;

    public function __construct(TownFetcher $townFetcher)
    {
        $this->phonemob = new Phonemob('');
        $this->town = new Town(598, $townFetcher->findTownsById(598)->getTownFullName());
        $this->optID = Opt::DEFAULT_OPT_ID;
    }
}
