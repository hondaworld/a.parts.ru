<?php

namespace App\Model\Expense\Entity\Type;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseTypeRepository::class)
 * @ORM\Table(name="expenseTypes")
 */
class ExpenseType
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
     * @ORM\Column(type="boolean", name="isSms")
     */
    private $isSms;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isSms(): bool
    {
        return $this->isSms;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }
}
