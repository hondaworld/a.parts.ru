<?php

namespace App\Model\Menu\Entity\Action;

use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Menu\Entity\Section\MenuSection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=MenuActionRepository::class)
 * @ORM\Table(name="menu_actions")
 */
class MenuAction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var MenuSection
     * @ORM\ManyToOne(targetEntity="App\Model\Menu\Entity\Section\MenuSection", inversedBy="actions")
     * @ORM\JoinColumn(name="menu_section_id", referencedColumnName="id", nullable=false)
     */
    private $section;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $icon;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ManagerGroup[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Manager\Entity\Group\ManagerGroup", mappedBy="actions")
     */
    private $groups;

    public function __construct(MenuSection $section, string $name, ?string $label, ?string $icon, string $url = '')
    {
        $this->section = $section;
        $this->name = $name;
        $this->label = $label ?: '';
        $this->icon = $icon ?: '';
        $this->url = $url;
    }

    public function update(string $name, ?string $label, ?string $icon): void
    {
        $this->name = $name;
        $this->label = $label ?: '';
        $this->icon = $icon ?: '';
    }

    public function isForAction(string $name): bool
    {
        return $this->name === $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): MenuSection
    {
        return $this->section;
    }

    public function changeSection(MenuSection $section): void
    {
        $this->section = $section;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }
}
