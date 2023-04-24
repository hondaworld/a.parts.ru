<?php

namespace App\Model\Manager\Entity\Type;

use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @ORM\Entity(repositoryClass=ManagerTypeRepository::class)
 * @ORM\Table(name="managerTypes")
 */
class ManagerType
{
    public const DEFAULT_ID = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="managerTypeID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var Manager
     * @OneToMany(targetEntity="App\Model\Manager\Entity\Manager\Manager", mappedBy="type")
     */
    private $managers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
