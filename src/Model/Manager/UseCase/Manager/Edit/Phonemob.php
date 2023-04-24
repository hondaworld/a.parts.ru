<?php


namespace App\Model\Manager\UseCase\Manager\Edit;

use Symfony\Component\Validator\Constraints as Assert;

class Phonemob
{
    public $countryPhone;

    public $phonemob;

    public function __construct(string $phonemob, string $countryPhone = '')
    {
        $this->countryPhone = $countryPhone;
        $this->phonemob = $phonemob;
    }

}