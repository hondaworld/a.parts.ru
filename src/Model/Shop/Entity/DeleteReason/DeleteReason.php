<?php

namespace App\Model\Shop\Entity\DeleteReason;

use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeleteReasonRepository::class)
 * @ORM\Table(name="deleteReasons")
 */
class DeleteReason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="deleteReasonID")
     */
    private $deleteReasonID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="deleteReason")
     */
    private $incomes;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="deleteReason")
     */
    private $order_goods;

    public function __construct(string $name, bool $isMain)
    {
        $this->name = $name;
        $this->isMain = $isMain;
    }

    public function update(string $name, bool $isMain)
    {
        $this->name = $name;
        $this->isMain = $isMain;
    }

    public function getId(): int
    {
        return $this->deleteReasonID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function getNoneDelete(): ?bool
    {
        return $this->noneDelete;
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
