<?php

namespace App\Model\Document\UseCase\Type\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="5",
     *     minMessage="Короткое наименование должно быть не больше 5 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\Length(
     *     max="50",
     *     minMessage="Файл должен быть не больше 50 символов"
     * )
     */
    public $path;
}
