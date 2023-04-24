<?php

namespace App\Model\Ticket\Entity\ClientTicket;

use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Order\Order;
use App\Model\Order\Entity\Site\Site;
use App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswer;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientTicketRepository::class)
 * @ORM\Table(name="client_tickets")
 */
class ClientTicket
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ticketID")
     */
    private $ticketID;

    /**
     * @var Site
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Site\Site", inversedBy="tickets")
     * @ORM\JoinColumn(name="siteID", referencedColumnName="siteID", nullable=true)
     */
    private $site;

    /**
     * @ORM\Column(type="integer")
     */
    private $ticket_num;

    /**
     * @var ClientTicketGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup", inversedBy="tickets")
     * @ORM\JoinColumn(name="groupID", referencedColumnName="groupID", nullable=true)
     */
    private $group;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="client_tickets")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $user_email = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $user_subject = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $user_name = '';

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $user_phone = '';

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="client_tickets")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofclosed;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager")
     * @ORM\JoinColumn(name="managerofclosed", referencedColumnName="managerID", nullable=true)
     */
    private $managerofclosed;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofdeleted;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager")
     * @ORM\JoinColumn(name="managerofdeleted", referencedColumnName="managerID", nullable=true)
     */
    private $managerofdeleted;

    /**
     * @ORM\Column(type="boolean", name="isRead")
     */
    private $isRead = false;

    /**
     * @ORM\Column(type="boolean", name="isReadClient")
     */
    private $isReadClient = true;

    /**
     * @ORM\Column(type="integer")
     */
    private $answer = -1;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofanswer;

    /**
     * @var Auto
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Auto\Auto")
     * @ORM\JoinColumn(name="autoID", referencedColumnName="autoID", nullable=true)
     */
    private $auto;

    /**
     * @var Order
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Order\Order")
     * @ORM\JoinColumn(name="ticket_orderID", referencedColumnName="orderID", nullable=true)
     */
    private $ticket_order;

    /**
     * @var ClientTicketAnswer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswer", mappedBy="ticket", orphanRemoval=true)
     */
    private $answers;

    public function __construct(ClientTicketGroup $group, User $user, int $ticket_num, string $user_subject)
    {
        $this->group = $group;
        $this->user = $user;
        $this->ticket_num = $ticket_num;
        $this->user_subject = $user_subject;
        $this->dateofadded = new \DateTime();
        $this->dateofanswer = new \DateTime();
    }

    public function delete(Manager $manager): void
    {
        $this->dateofdeleted = new \DateTime();
        $this->managerofdeleted = $manager;
    }

    public function close(Manager $manager): void
    {
        $this->dateofclosed = new \DateTime();
        $this->managerofclosed = $manager;
    }

    public function getId(): ?int
    {
        return $this->ticketID;
    }

    public function open(Manager $manager): void
    {
        $this->manager = $manager;
    }

    public function read(): void
    {
        $this->isRead = true;
    }

    public function answering(Manager $manager): void
    {
        $this->dateofanswer = new \DateTime();
        $this->answer = $manager->getId();
        $this->isReadClient = false;
        $this->dateofclosed = null;
        $this->managerofclosed = null;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function getTicketNum(): int
    {
        return $this->ticket_num;
    }

    public function getGroup(): ?ClientTicketGroup
    {
        return $this->group;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserEmail(): string
    {
        return $this->user_email;
    }

    public function getUserSubject(): string
    {
        return $this->user_subject;
    }

    public function getUserName(): string
    {
        return $this->user_name;
    }

    public function getUserPhone(): string
    {
        return $this->user_phone;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getDateofclosed(): ?\DateTimeInterface
    {
        return $this->dateofclosed;
    }

    public function getManagerofclosed(): ?Manager
    {
        return $this->managerofclosed;
    }

    public function getDateofdeleted(): ?\DateTimeInterface
    {
        return $this->dateofdeleted;
    }

    public function getManagerofdeleted(): ?Manager
    {
        return $this->managerofdeleted;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function isReadClient(): bool
    {
        return $this->isReadClient;
    }

    public function getAnswer(): int
    {
        return $this->answer;
    }

    public function getDateofanswer(): ?\DateTimeInterface
    {
        return $this->dateofanswer;
    }

    public function getAuto(): ?Auto
    {
        return $this->auto;
    }

    public function getTicketOrder(): ?Order
    {
        return $this->ticket_order;
    }

    /**
     * @return string
     */
    public function getClientName(): string
    {
        if ($this->user) {
            return $this->user->getName();
        } elseif ($this->user_email != '') {
            return $this->user_name != '' ? $this->user_name . " <" . $this->user_email . ">" : $this->user_email;
        } else {
            return $this->user_name != '' ? $this->user_name : 'Клиент';
        }
    }

    /**
     * @return ClientTicketAnswer[]|ArrayCollection
     */
    public function getAnswers()
    {
        $arr = $this->answers->toArray();
        usort($arr, function (ClientTicketAnswer $a, ClientTicketAnswer $b) {
            return $b->getDateofadded() <=> $a->getDateofadded();
        });
        return $arr;
    }

    public function isOpened(): bool
    {
        return $this->manager != null;
    }
}
