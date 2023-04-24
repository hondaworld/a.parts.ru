<?php

namespace App\Model\Manager\Entity\Auth;

use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ManagerAuthRepository::class)
 * @ORM\Table(name="managerAuth")
 */
class ManagerAuth
{
    public const TYPE_ENTER = 1;
    public const TYPE_EXIT = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="authID")
     */
    private $authID;
    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $ip;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    public function __construct(Manager $manager, string $ip, int $type)
    {
        $this->manager = $manager;
        $this->ip = $ip;
        $this->type = $type;
        $this->dateofadded = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->authID;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getType(): ?int
    {
        return $this->type;
    }
}
