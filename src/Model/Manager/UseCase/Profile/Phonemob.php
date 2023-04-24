<?php


namespace App\Model\Manager\UseCase\Profile;

use Symfony\Component\Validator\Constraints as Assert;

class Phonemob
{
    public $countryPhone;

    /**
     * @Assert\NotBlank(
     *     message="Введите мобильный телефон"
     * )
     */
    public $phonemob;

    public function __construct(string $phonemob, string $countryPhone = '')
    {
        $this->countryPhone = $countryPhone;
        $this->phonemob = $phonemob;
    }

}