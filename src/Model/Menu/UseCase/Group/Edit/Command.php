<?php

namespace App\Model\Menu\UseCase\Group\Edit;

use App\Model\Menu\Entity\Group\MenuGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $id;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\Length(
     *     max="100",
     *     maxMessage="Максимум 100 символов"
     * )
     */
    public $icon;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromMenuGroup(MenuGroup $menuGroup): self
    {
        $command = new self($menuGroup->getId());
        $command->name = $menuGroup->getName();
        $command->icon = $menuGroup->getIcon();
        return $command;
    }
}
