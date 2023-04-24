<?php

namespace App\Model\Sklad\UseCase\Parts\Location;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $location;

    public $isCreate;
}
