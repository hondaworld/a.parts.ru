<?php

namespace App\Model\Document\Entity\Document;

use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 * @ORM\Table(name="documents")
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="documentID")
     */
    private $documentID;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="documents")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="documents")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="documents")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;


    /**
     * @var DocumentIdentification
     * @ORM\ManyToOne(targetEntity="App\Model\Document\Entity\Identification\DocumentIdentification", inversedBy="documents")
     * @ORM\JoinColumn(name="doc_identID", referencedColumnName="doc_identID", nullable=false)
     */
    private $identification;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $serial;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $number;

    /**
     * @ORM\Column(type="text")
     */
    private $organization;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofdoc;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    public function __construct(?object $object, DocumentIdentification $identification, string $serial, string $number, ?string $organization, ?\DateTime $dateofdoc, ?string $description, bool $isMain)
    {
        $this->identification = $identification;
        $this->serial = $serial;
        $this->number = $number;
        $this->organization = $organization ?: '';
        $this->dateofdoc = $dateofdoc;
        $this->description = $description ?: '';
        $this->isMain = $isMain;
        if ($object instanceof Manager) $this->manager = $object;
        if ($object instanceof User) $this->user = $object;
        if ($object instanceof Firm) $this->firm = $object;
    }

    public function update(DocumentIdentification $identification, string $serial, string $number, ?string $organization, ?\DateTime $dateofdoc, ?string $description, bool $isMain)
    {
        $this->identification = $identification;
        $this->serial = $serial;
        $this->number = $number;
        $this->organization = $organization ?: '';
        $this->dateofdoc = $dateofdoc;
        $this->description = $description ?: '';
        $this->isMain = $isMain;
    }

    public function clearMain()
    {
        $this->isMain = false;
    }

    public function getId(): ?int
    {
        return $this->documentID;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(Manager $manager): void
    {
        $this->manager = $manager;
    }

    public function getFirm(): ?Firm
    {
        return $this->firm;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getIdentification(): DocumentIdentification
    {
        return $this->identification;
    }

    public function getSerial(): ?string
    {
        return $this->serial;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function getDateofdoc(): ?\DateTimeInterface
    {
        if ($this->dateofdoc && $this->dateofdoc->format('Y') == '-0001') return null;
        return $this->dateofdoc;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function hide()
    {
        $this->isHide = true;
    }

    public function unhide()
    {
        $this->isHide = false;
    }
}
