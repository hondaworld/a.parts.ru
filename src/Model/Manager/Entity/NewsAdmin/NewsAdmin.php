<?php

namespace App\Model\Manager\Entity\NewsAdmin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NewsAdminRepository::class)
 * @ORM\Table(name="news_admin")
 */
class NewsAdmin
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="newsID")
     */
    private $newsID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="text")
     */
    private $description = '';

    /**
     * @ORM\Column(type="text")
     */
    private $text = '';

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="integer")
     */
    private $type = 1;

    public function __construct(string $name, string $description, int $type)
    {
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->dateofadded = new \DateTime();
    }

    public function update(string $name, string $description, int $type, \DateTime $dateofadded)
    {
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->dateofadded = $dateofadded;
    }

    public function getId(): ?int
    {
        return $this->newsID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDateofadded(): \DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    public function getType(): int
    {
        return $this->type;
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
