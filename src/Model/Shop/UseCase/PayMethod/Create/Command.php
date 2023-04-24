<?php

namespace App\Model\Shop\UseCase\PayMethod\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="150",
     *     exactMessage="Наименование должно быть не больше 150 символов"
     * )
     */
    public $val;

    public $description;

    public $isMain;
}
