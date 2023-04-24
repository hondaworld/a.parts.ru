<?php

namespace App\Model\Order\UseCase;

class Firm
{
    public $id;

    public $contactID;

    public $beznalID;

    public function __construct(int $id = 0, int $contactID = 0, int $beznalID = 0)
    {
        $this->id = $id;
        $this->contactID = $contactID;
        $this->beznalID = $beznalID;
    }
}