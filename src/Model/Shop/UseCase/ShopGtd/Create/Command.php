<?php

namespace App\Model\Shop\UseCase\ShopGtd\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\AtLeastOneOf({
     *     @Assert\Regex(pattern="#^([0-9]){8}(/){1}([0-9]){6}(/){1}(.){7}$#", message="00000000/000000/*******"),
     *     @Assert\Regex(pattern="#^([0-9]){8}(/){1}([0-9]){6}(/){1}([0-9]){7}(/){1}([0-9]){1,3}$#", message="00000000/000000/0000000/000"),
     *     @Assert\Regex(pattern="#^([\-]){10}$#", message="----------"),
     * }, message="Допустимые значения")
     */
    public $name;
}
