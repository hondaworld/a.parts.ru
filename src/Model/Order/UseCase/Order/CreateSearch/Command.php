<?php

namespace App\Model\Order\UseCase\Order\CreateSearch;

use App\Model\User\Entity\Opt\Opt;
use App\Model\User\UseCase\User\Phonemob;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var Phonemob
     * @Assert\Valid()
     */
    public $phonemob;

    public function __construct()
    {
        $this->phonemob = new Phonemob('');
    }
}
