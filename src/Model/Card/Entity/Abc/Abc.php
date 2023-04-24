<?php

namespace App\Model\Card\Entity\Abc;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AbcRepository::class)
 * @ORM\Table(name="abc")
 */
class Abc
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="abcID")
     */
    private $abcID;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $abc;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    public function __construct(string $abc, string $description)
    {
        $this->abc = $abc;
        $this->description = $description;
    }

    public function update(string $abc, string $description)
    {
        $this->abc = $abc;
        $this->description = $description;
    }

    public function getId(): int
    {
        return $this->abcID;
    }

    public function getAbc(): string
    {
        return $this->abc;
    }

    public function getDescription(): string
    {
        return $this->description;
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
