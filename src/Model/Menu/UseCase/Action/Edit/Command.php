<?php

namespace App\Model\Menu\UseCase\Action\Edit;

use App\Model\Menu\Entity\Action\MenuAction;
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

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromMenuAction(MenuAction $menuAction): self
    {
        $command = new self($menuAction->getId());
        $command->name = $menuAction->getName();
        $command->label = $menuAction->getLabel();
        $command->icon = $menuAction->getIcon();
        $command->section = $menuAction->getSection();
        return $command;
    }
}
