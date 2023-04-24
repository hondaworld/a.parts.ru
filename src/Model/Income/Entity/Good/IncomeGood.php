<?php

namespace App\Model\Income\Entity\Good;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncomeGoodRepository::class)
 * @ORM\Table(name="income_goods")
 */
class IncomeGood
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="goodID")
     */
    private $goodID;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="income_goods")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=true)
     */
    private $income;

    /**
     * @var IncomeDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", inversedBy="income_goods")
     * @ORM\JoinColumn(name="incomeDocumentID", referencedColumnName="incomeDocumentID", nullable=true)
     */
    private $income_document;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="income_goods")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="income_goods")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="income_goods")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $returning_reason;

    public function __construct(Income $income, IncomeDocument $income_document, ZapSklad $zapSklad, ZapCard $zapCard, Manager $manager, int $quantity, string $returning_reason)
    {
        $this->dateofadded = new \DateTime();
        $this->income = $income;
        $this->income_document = $income_document;
        $this->zapSklad = $zapSklad;
        $this->zapCard = $zapCard;
        $this->manager = $manager;
        $this->quantity = $quantity;
        $this->returning_reason = $returning_reason;
    }

    public function getId(): ?int
    {
        return $this->goodID;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getIncomeDocument(): IncomeDocument
    {
        return $this->income_document;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getDateofadded(): \DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getReturningReason(): string
    {
        return $this->returning_reason;
    }
}
