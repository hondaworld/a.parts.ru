<?php


namespace App\Model\User\UseCase\User;

use Symfony\Component\Validator\Constraints as Assert;

class Phonemob
{
    public $countryPhone;

    /**
     * @Assert\NotBlank()
     */
    public $phonemob;

    public function __construct(string $phonemob, string $countryPhone = '')
    {
        $this->countryPhone = $countryPhone;
        $this->phonemob = $phonemob;
    }

    public function getValue()
    {
        return preg_replace('/[^0-9+]/', '', $this->phonemob);
    }
}