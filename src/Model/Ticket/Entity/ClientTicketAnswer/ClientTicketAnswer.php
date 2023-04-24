<?php

namespace App\Model\Ticket\Entity\ClientTicketAnswer;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\Model\Ticket\Entity\ClientTicketAttach\ClientTicketAttach;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientTicketAnswerRepository::class)
 * @ORM\Table(name="client_ticket_answers")
 */
class ClientTicketAnswer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="answerID")
     */
    private $answerID;

    /**
     * @var ClientTicket
     * @ORM\ManyToOne(targetEntity="App\Model\Ticket\Entity\ClientTicket\ClientTicket", inversedBy="answers")
     * @ORM\JoinColumn(name="ticketID", referencedColumnName="ticketID")
     */
    private $ticket;

    /**
     * @ORM\Column(type="integer")
     */
    private $manager = -1;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\Column(type="boolean", name="isShow")
     */
    private $isShow = true;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $ip = '';

    /**
     * @var ClientTicketAttach[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Ticket\Entity\ClientTicketAttach\ClientTicketAttach", mappedBy="answer", orphanRemoval=true, cascade={"persist"})
     */
    private $attaches;

    public function __construct(ClientTicket $ticket, string $text, Manager $manager)
    {
        $this->ticket = $ticket;
        $this->text = $text;
        $this->manager = $manager->getId();
        $this->dateofadded = new \DateTime();
        $this->attaches = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->answerID;
    }

    public function getTicket(): ClientTicket
    {
        return $this->ticket;
    }

    public function getManager(): int
    {
        return $this->manager;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isShow(): bool
    {
        return $this->isShow;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return ClientTicketAttach[]|ArrayCollection
     */
    public function getAttaches()
    {
        return $this->attaches->toArray();
    }

    public function addAttach(ClientTicketAttach $clientTicketAttach): void
    {
        $clientTicketAttach->updateAnswer($this);
        $this->attaches->add($clientTicketAttach);
    }
}
