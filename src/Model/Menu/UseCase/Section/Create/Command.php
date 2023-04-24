<?php

namespace App\Model\Menu\UseCase\Section\Create;

use App\Model\Menu\Entity\Group\MenuGroup;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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

    public $sort;

    public $group;

    public $parent_id;

    public $actions;

    public function __construct(MenuGroup $group, int $parent_id)
    {
        $this->group = $group;
        $this->parent_id = $parent_id;
    }
}
