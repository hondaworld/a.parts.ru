<?php

namespace App\Model\Beznal\UseCase\Beznal\Edit;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Beznal\UseCase\Beznal\Bank;
use App\ReadModel\Beznal\BankFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $beznalID;

    /**
     * @Assert\Valid()
     */
    public $bank;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $rasschet;

    public $description;

    public $isMain;

    public $manager;

    public $user;

    public $firm;

    public function __construct(int $beznalID) {
        $this->beznalID = $beznalID;
    }

    public static function fromBeznal(Beznal $beznal, BankFetcher $bankFetcher): self
    {
        $command = new self($beznal->getId());
        $command->manager = $beznal->getManager();
        $command->user = $beznal->getUser();
        $command->firm = $beznal->getFirm();
        $command->bank = new Bank($beznal->getBank()->getId(), $bankFetcher->findBankById($beznal->getBank()->getId())->getBankFullName());
        $command->rasschet = $beznal->getRasschet();
        $command->description = $beznal->getDescription();
        $command->isMain = $beznal->isMain();
        return $command;
    }
}
