<?php

namespace App\Model\Shop\Entity\Reseller;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResellerRepository::class)
 * @ORM\Table(name="resellers")
 */
class Reseller
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="reseller")
     */
    private $expenseDocuments;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    /**
     * @return ExpenseDocument[]|ArrayCollection
     */
    public function getExpenseDocuments()
    {
        return $this->expenseDocuments;
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
