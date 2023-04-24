<?php

namespace App\Model\Document\Entity\Identification;

use App\Model\Document\Entity\Document\Document;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentIdentificationRepository::class)
 * @ORM\Table(name="doc_idents")
 */
class DocumentIdentification
{
    public const PASSPORT_ID = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="doc_identID")
     */
    private $doc_identID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var Document[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Document\Entity\Document\Document", mappedBy="identification")
     */
    private $documents;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->doc_identID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function hide()
    {
        $this->isHide = true;
    }

    public function unhide()
    {
        $this->isHide = false;
    }

    public function getDocuments()
    {
        return $this->documents->toArray();
    }
}
