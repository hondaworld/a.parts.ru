<?php

namespace App\Model\Auto\Entity\Engine;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AutoEngineRepository::class)
 * @ORM\Table(name="auto_engine")
 */
class AutoEngine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="auto_engineID")
     */
    private $auto_engineID;

    /**
     * @var AutoGeneration
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Generation\AutoGeneration", inversedBy="engines")
     * @ORM\JoinColumn(name="auto_generationID", referencedColumnName="auto_generationID", nullable=false)
     */
    private $generation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     */
    private $description_tuning;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    public function __construct(AutoGeneration $generation, $name, string $url, ?string $description_tuning)
    {
        $this->generation = $generation;
        $this->name = $name;
        $this->url = $url;
        $this->description_tuning = $description_tuning ?: '';
    }

    public function update(string $name, string $url, ?string $description_tuning)
    {
        $this->name = $name;
        $this->url = $url;
        $this->description_tuning = $description_tuning ?: '';
    }

    public function getId(): ?int
    {
        return $this->auto_engineID;
    }

    public function getAutoGeneration(): AutoGeneration
    {
        return $this->generation;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getDescriptionTuning(): ?string
    {
        return $this->description_tuning;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
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
