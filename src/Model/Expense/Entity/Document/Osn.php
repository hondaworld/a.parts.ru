<?php


namespace App\Model\Expense\Entity\Document;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Osn
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $number;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    public function __construct(?string $name = '', ?string $number = '', ?\DateTime $dateofadded = null)
    {
        $this->name = $name ?: '';
        $this->number = $number ?: '';
        $this->dateofadded = $dateofadded;
    }

    public function update(?string $name, ?string $number, ?\DateTime $dateofadded)
    {
        $this->name = $name ?: '';
        $this->number = $number ?: '';
        $this->dateofadded = $dateofadded;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @return \DateTime
     */
    public function getDateofadded(): ?\DateTimeInterface
    {
        if ($this->dateofadded && $this->dateofadded->format('Y') == '-0001') return null;
        return $this->dateofadded;
    }

    public function getOsnName(): string
    {
        if ($this->getName() == '') return '';
        $osn = $this->getName();
        if ($this->getNumber() != '') {
            $osn .= ' №' . $this->getNumber();
            if ($this->getDateofadded()) {
                $osn .= ' от ' . $this->getDateofadded()->format('d.m.Y');
            }
        }
        return $osn;
    }
}