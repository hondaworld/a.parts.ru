<?php

namespace App\Model\Manager\UseCase\FavouriteMenu\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="30",
     *     maxMessage="Максимум 30 символов"
     * )
     */
    public $name;

    /**
     * @var string
     */
    public $url;

    public $menu_section_id;

    public $sort;

    public $dropDownList;
}
