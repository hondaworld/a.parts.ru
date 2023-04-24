<?php

namespace App\Model\Menu\UseCase\Section\Edit;

use App\Model\Menu\Entity\Section\MenuSection;
use App\ReadModel\DropDownList;
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

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     * @Assert\Length(
     *     max="50",
     *     maxMessage="Максимум 50 символов"
     * )
     */
    public $entity;

    /**
     * @var string
     */
    public $pattern;

    public $group;

    public $parent_id;

    public $parent;

    public $dropDownList;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromMenuSection(MenuSection $menuSection): self
    {
        $command = new self($menuSection->getId());
        $command->name = $menuSection->getName();
        $command->icon = $menuSection->getIcon();
        $command->url = $menuSection->getUrl();
        $command->group = $menuSection->getGroup();
        $command->parent_id = $menuSection->getParentId();
        $command->entity = $menuSection->getEntity();
        $command->pattern = $menuSection->getPattern();
        $command->parent = new DropDownList($command->group->getId() . ',' . $command->parent_id, '');
        return $command;
    }
}
