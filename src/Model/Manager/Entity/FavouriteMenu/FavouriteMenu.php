<?php

namespace App\Model\Manager\Entity\FavouriteMenu;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Menu\Entity\Section\MenuSection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FavouriteMenuRepository::class)
 * @ORM\Table(name="favouriteMenu")
 */
class FavouriteMenu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="favouriteMenus")
     * @ORM\JoinColumn(name="manager_id", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @var MenuSection
     * @ORM\ManyToOne(targetEntity="App\Model\Menu\Entity\Section\MenuSection", inversedBy="favouriteMenus")
     * @ORM\JoinColumn(name="menu_section_id", referencedColumnName="id", nullable=true)
     */
    private $menu_section;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    public function __construct(Manager $manager, string $name, ?MenuSection $menuSection, ?string $url, int $sort)
    {
        $this->manager = $manager;
        $this->name = $name;
        $this->menu_section = $menuSection;
        $this->url = $url ?: '';
        $this->sort = $sort;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getMenuSection(): MenuSection
    {
        return $this->menu_section;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function changeSort(int $sort): void
    {
        $this->sort = $sort;
    }
}
