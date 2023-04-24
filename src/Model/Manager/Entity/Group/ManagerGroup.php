<?php

namespace App\Model\Manager\Entity\Group;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Menu\Entity\Action\MenuAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=ManagerGroupRepository::class)
 * @ORM\Table(name="managerGroups")
 */
class ManagerGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="managerGroupID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var Manager[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Manager\Entity\Manager\Manager", mappedBy="groups")
     */
    private $managers;

    /**
     * @var MenuAction[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Menu\Entity\Action\MenuAction", inversedBy="groups")
     * @ORM\JoinTable(name="linkMenuActionManagerGroup",
     *      joinColumns={@ORM\JoinColumn(name="manager_group_id", referencedColumnName="managerGroupID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="menu_action_id", referencedColumnName="id")}
     * )
     */
    private $actions;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function update(string $name, array $actions): void
    {
        $this->name = $name;
        $this->actions = new ArrayCollection($actions);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return MenuAction[]|ArrayCollection
     */
    public function getActions()
    {
        return $this->actions->toArray();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }
}
