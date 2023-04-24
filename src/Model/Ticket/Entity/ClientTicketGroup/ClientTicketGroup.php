<?php

namespace App\Model\Ticket\Entity\ClientTicketGroup;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\Model\Ticket\Entity\ClientTicketTemplate\ClientTicketTemplate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=ClientTicketGroupRepository::class)
 * @ORM\Table(name="client_ticket_groups")
 */
class ClientTicketGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="groupID")
     */
    private $groupID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isHideUser")
     */
    private $isHideUser = false;

    /**
     * @ORM\Column(type="boolean", name="isClose")
     */
    private $isClose = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var Manager[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="client_ticket_groups")
     * @ORM\JoinTable(name="client_ticket_group_managers",
     *      joinColumns={@ORM\JoinColumn(name="groupID", referencedColumnName="groupID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="managerID", referencedColumnName="managerID")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $managers;

    /**
     * @var ClientTicketTemplate[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Ticket\Entity\ClientTicketTemplate\ClientTicketTemplate", mappedBy="client_ticket_groups")
     */
    private $client_ticket_templates;

    /**
     * @var ClientTicket[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Ticket\Entity\ClientTicket\ClientTicket", mappedBy="group")
     */
    private $tickets;

    public function __construct(string $name, bool $isHideUser, bool $isClose)
    {
        $this->name = $name;
        $this->isHideUser = $isHideUser;
        $this->isClose = $isClose;
        $this->managers = new ArrayCollection();
    }

    public function update(string $name, bool $isHideUser, bool $isClose)
    {
        $this->name = $name;
        $this->isHideUser = $isHideUser;
        $this->isClose = $isClose;
    }

    public function getId(): int
    {
        return $this->groupID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isHideUser(): bool
    {
        return $this->isHideUser;
    }

    public function isClose(): bool
    {
        return $this->isClose;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    /**
     * @return ArrayCollection|Manager[]
     */
    public function getManagers()
    {
        return $this->managers->toArray();
    }

    public function clearManagers(): void
    {
        $this->managers->clear();
    }

    /**
     * @param Manager $manager
     */
    public function assignManager(Manager $manager): void
    {
        $this->managers->add($manager);
    }

    /**
     * @return ClientTicketTemplate[]|ArrayCollection
     */
    public function getClientTicketTemplates()
    {
        return $this->client_ticket_templates->toArray();
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
     * @return ClientTicket[]|ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets->toArray();
    }
}
