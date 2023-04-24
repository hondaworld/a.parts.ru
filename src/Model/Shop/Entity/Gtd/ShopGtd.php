<?php

namespace App\Model\Shop\Entity\Gtd;

use App\Model\Income\Entity\Income\Income;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopGtdRepository::class)
 * @ORM\Table(name="shop_gtd")
 */
class ShopGtd
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="shop_gtdID")
     */
    private $shop_gtdID;

    /**
     * @var Gtd
     * @ORM\Column(type="gtd", length=255)
     */
    private $name;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="shop_gtd")
     */
    private $incomes;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="shop_gtd1")
     */
    private $incomes1;

    public function __construct(Gtd $name)
    {
        $this->name = $name;
    }

    public function update(Gtd $name)
    {
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->shop_gtdID;
    }

    public function getName(): Gtd
    {
        return $this->name;
    }

    /**
     * @return Income[]|ArrayCollection
     */
    public function getIncomes()
    {
        return $this->incomes->toArray();
    }

    /**
     * @return Income[]|ArrayCollection
     */
    public function getIncomes1()
    {
        return $this->incomes1->toArray();
    }


}
