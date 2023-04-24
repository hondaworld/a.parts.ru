<?php

namespace App\Model\Card\UseCase\Measure\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="10",
     *     minMessage="Краткое наименование должно не больше 10 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\Length(
     *     max="5",
     *     minMessage="Код ОКЕИ должно не больше 10 символов"
     * )
     */
    public $okei;
}
