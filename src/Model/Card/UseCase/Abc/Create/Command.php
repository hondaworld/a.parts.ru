<?php

namespace App\Model\Card\UseCase\Abc\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="2",
     *     minMessage="Краткое наименование должно быть не больше 2 символа"
     * )
     * @Assert\Regex(
     *     pattern="/^[a-zA-Z0-9]+$/",
     *     message="Значение должна быть латинской буквой или цифрой"
     * )
     */
    public $abc;

    /**
     * @var string
     */
    public $description;
}
