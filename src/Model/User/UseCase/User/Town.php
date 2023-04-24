<?php


namespace App\Model\User\UseCase\User;

use Symfony\Component\Validator\Constraints as Assert;

class Town
{
    public $id;

    public $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}