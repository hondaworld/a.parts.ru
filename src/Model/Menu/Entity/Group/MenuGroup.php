<?php

namespace App\Model\Menu\Entity\Group;

use App\Model\Menu\Entity\Section\MenuSection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MenuGroupRepository::class)
 * @ORM\Table(name="menu_groups")
 */
class MenuGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $icon;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort = 0;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var MenuSection[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Menu\Entity\Section\MenuSection", mappedBy="group", orphanRemoval=true, cascade={"persist"})
     */
    private $sections;

    public function __construct(string $name, ?string $icon, int $sort)
    {
        $this->name = $name;
        $this->icon = $icon ?: '';
        $this->sort = $sort;
    }

    public function update(string $name, ?string $icon): void
    {
        $this->name = $name;
        $this->icon = $icon ?: '';
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

    public function getSort(): ?int
    {
        return $this->sort;
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

    /**
     * @return MenuSection[]
     */
    public function getSections(): array
    {
        return $this->sections->toArray();
    }
}
