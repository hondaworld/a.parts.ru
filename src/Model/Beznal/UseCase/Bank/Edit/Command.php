<?php

namespace App\Model\Beznal\UseCase\Bank\Edit;

use App\Model\Beznal\Entity\Bank\Bank;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $bankID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $bik;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $korschet;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $address;

    /**
     * @var string
     */
    public $description;

    public function __construct(int $bankID) {
        $this->bankID = $bankID;
    }

    public static function fromEntity(Bank $bank): self
    {
        $command = new self($bank->getId());
        $command->bik = $bank->getBik();
        $command->name = $bank->getName();
        $command->korschet = $bank->getKorschet();
        $command->address = $bank->getAddress();
        $command->description = $bank->getDescription();
        return $command;
    }
}
