<?php

namespace App\Model\Menu\UseCase\Action\Create;

use App\Model\Menu\Entity\Section\MenuSection;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $label;

    /**
     * @var string
     * @Assert\Length(
     *     max="100",
     *     maxMessage="Максимум 100 символов"
     * )
     */
    public $icon;

    public $section;

    public function __construct(MenuSection $section)
    {
        $this->section = $section;
    }
}
