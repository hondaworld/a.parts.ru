<?php

namespace App\Model\Ticket\Entity\ClientTicketAttach;

use App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswer;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientTicketAttachRepository::class)
 * @ORM\Table(name="client_ticket_attaches")
 */
class ClientTicketAttach
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="attachID")
     */
    private $attachID;

    /**
     * @var ClientTicketAnswer
     * @ORM\ManyToOne(targetEntity="App\Model\Ticket\Entity\ClientTicketAnswer\ClientTicketAnswer", inversedBy="attaches")
     * @ORM\JoinColumn(name="answerID", referencedColumnName="answerID")
     */
    private $answer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $filename = '';

    public function __construct(string $name, string $filename = '')
    {
        $this->name = $name;
        $this->filename = $filename;
    }

    public function updateAnswer(ClientTicketAnswer $clientTicketAnswer): void
    {
        $this->answer = $clientTicketAnswer;
    }

    public function getId(): ?int
    {
        return $this->attachID;
    }

    public function getAnswer(): ClientTicketAnswer
    {
        return $this->answer;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getFile(string $dir): ?string
    {
        if (strpos($this->name, "http://") === false && strpos($this->name, "https://") === false) {
            $filename = $dir . $this->name;
            if (file_exists('.' . $filename)) {
                return $filename;
            }
            return 'http://admin.parts.ru/upload/ticket_attach/' . $this->answer->getId() . '/' . rawurlencode($this->name);
        } else {
            return $this->name;
        }
    }
}
