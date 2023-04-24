<?php

namespace App\Model\Expense\Entity\SchetFakPrint;

use App\Model\Expense\Entity\SchetFak\SchetFak;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SchetFakPrintRepository::class)
 * @ORM\Table(name="schet_fak_print")
 */
class SchetFakPrint
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="schet_fak_printID")
     */
    private $schet_fak_printID;

    /**
     * @var SchetFak
     * @ORM\OneToOne(targetEntity="App\Model\Expense\Entity\SchetFak\SchetFak", inversedBy="schet_fak_print")
     * @ORM\JoinColumn(name="schet_fakID", referencedColumnName="schet_fakID", nullable=false)
     */
    private $schet_fak;

    /**
     * @var From
     * @ORM\Embedded(class="From", columnPrefix="from_")
     */
    private $from;

    /**
     * @var FromGruz
     * @ORM\Embedded(class="FromGruz", columnPrefix="from_")
     */
    private $from_gruz;

    /**
     * @var To
     * @ORM\Embedded(class="To", columnPrefix="to_")
     */
    private $to;

    /**
     * @var ToGruz
     * @ORM\Embedded(class="ToGruz", columnPrefix="to_")
     */
    private $to_gruz;

    /**
     * @var ToCash
     * @ORM\Embedded(class="ToCash", columnPrefix="to_")
     */
    private $to_cash;

    public function __construct(SchetFak $schet_fak, From $from, FromGruz $from_gruz, To $to, ToGruz $to_gruz, ToCash $to_cash)
    {
        $this->schet_fak = $schet_fak;
        $this->from = $from;
        $this->from_gruz = $from_gruz;
        $this->to = $to;
        $this->to_gruz = $to_gruz;
        $this->to_cash = $to_cash;
    }

    public function getId(): int
    {
        return $this->schet_fak_printID;
    }

    /**
     * @return SchetFak
     */
    public function getSchetFak(): SchetFak
    {
        return $this->schet_fak;
    }

    /**
     * @return From
     */
    public function getFrom(): From
    {
        return $this->from;
    }

    /**
     * @return FromGruz
     */
    public function getFromGruz(): FromGruz
    {
        return $this->from_gruz;
    }

    /**
     * @return To
     */
    public function getTo(): To
    {
        return $this->to;
    }

    /**
     * @return ToGruz
     */
    public function getToGruz(): ToGruz
    {
        return $this->to_gruz;
    }

    /**
     * @return ToCash
     */
    public function getToCash(): ToCash
    {
        return $this->to_cash;
    }

}
