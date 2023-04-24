<?php

namespace App\Model\Ticket\Entity\ClientTicketTemplate;

use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientTicketTemplateRepository::class)
 * @ORM\Table(name="client_ticket_templates")
 */
class ClientTicketTemplate
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="templateID")
     */
    private $templateID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ClientTicketGroup[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup", inversedBy="client_ticket_templates")
     * @ORM\JoinTable(name="client_ticket_template_group",
     *      joinColumns={@ORM\JoinColumn(name="templateID", referencedColumnName="templateID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="groupID", referencedColumnName="groupID")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $client_ticket_groups;


    public function __construct(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
        $this->client_ticket_groups = new ArrayCollection();
    }

    public function update(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    public function getId(): int
    {
        return $this->templateID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    /**
     * @return ClientTicketGroup[]|ArrayCollection
     */
    public function getClientTicketGroups()
    {
        return $this->client_ticket_groups->toArray();
    }

    public function clearClientTicketGroups(): void
    {
        $this->client_ticket_groups->clear();
    }

    /**
     * @param ClientTicketGroup $clientTicketGroup
     */
    public function assignClientTicketGroup(ClientTicketGroup $clientTicketGroup): void
    {
        $this->client_ticket_groups->add($clientTicketGroup);
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }
}
