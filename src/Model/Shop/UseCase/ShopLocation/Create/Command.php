<?php

namespace App\Model\Shop\UseCase\ShopLocation\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="25",
     *     exactMessage="Короткое наименование должно быть не больше 25 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;
}
