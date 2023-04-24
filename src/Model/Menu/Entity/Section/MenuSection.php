<?php

namespace App\Model\Menu\Entity\Section;

use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu;
use App\Model\Menu\Entity\Action\MenuAction;
use App\Model\Menu\Entity\Group\MenuGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MenuSectionRepository::class)
 * @ORM\Table(name="menu_sections")
 */
class MenuSection
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var MenuGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Menu\Entity\Group\MenuGroup", inversedBy="sections")
     * @ORM\JoinColumn(name="menu_group_id", referencedColumnName="id", nullable=false)
     */
    private $group;

    /**
     * @ORM\Column(type="integer", name="parent_id")
     */
    private $parent_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $icon;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $entity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pattern;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var MenuAction[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Menu\Entity\Action\MenuAction", mappedBy="section", orphanRemoval=true, cascade={"persist"})
     */
    private $actions;

    /**
     * @var FavouriteMenu[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu", mappedBy="menu_section", orphanRemoval=true, cascade={"persist"})
     */
    private $favouriteMenus;

    public function __construct(MenuGroup $group, int $parent_id, string $name, ?string $icon, ?string $url, ?string $entity, ?string $pattern, int $sort)
    {
        $this->group = $group;
        $this->parent_id = $parent_id;
        $this->name = $name;
        $this->icon = $icon ?: '';
        $this->url = $url ?: '';
        $this->entity = $entity ?: '';
        $this->pattern = $pattern ?: '';
        $this->sort = $sort;
        $this->actions = new ArrayCollection();
    }

    public function update(string $name, ?string $icon, ?string $url, ?string $entity, ?string $pattern): void
    {
        $this->name = $name;
        $this->icon = $icon ?: '';
        $this->url = $url ?: '';
        $this->entity = $entity ?: '';
        $this->pattern = $pattern ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    public function changeParentId(int $parent_id): void
    {
        $this->parent_id = $parent_id;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function getGroup(): MenuGroup
    {
        return $this->group;
    }

    public function changeGroup(MenuGroup $group): void
    {
        $this->group = $group;
    }

    public function changeSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }

    public function attachAction(string $name, string $label, string $icon, string $url = ''): void
    {
        foreach ($this->actions as $existing) {
            if ($existing->isForAction($name)) {
                throw new \DomainException('Такая операция уже существует');
            }
        }
        $this->actions->add(new MenuAction($this, $name, $label, $icon, $url));
    }

    /**
     * @return MenuAction[]|ArrayCollection
     */
    public function getActions()
    {
        return $this->actions->toArray();
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function getPattern()
    {
        return $this->pattern;
    }
}
