<?php


namespace App\Model\User\UseCase\User;

use Symfony\Component\Validator\Constraints as Assert;

class User
{
    public $id;

    public $name;

    public $contactID;

    public $beznalID;

    public function __construct(int $id = 0, string $name = '', int $contactID = 0, int $beznalID = 0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->contactID = $contactID;
        $this->beznalID = $beznalID;
    }
}