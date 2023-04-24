<?php

namespace App\Model\User\UseCase\FirmContr\Edit;

use App\Model\Beznal\UseCase\Beznal\Bank;
use App\Model\Contact\UseCase\Contact\Address;
use App\Model\Contact\UseCase\Contact\Town;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\ReadModel\Beznal\BankFetcher;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $firmcontrID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $organization;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="12",
     *     max="12",
     *     exactMessage="ИНН должен содержать 12 цифр"
     * )
     */

    public $inn;

    /**
     * @var string
     * @Assert\Length(
     *     min="9",
     *     max="9",
     *     exactMessage="КПП должен содержать 9 цифр"
     * )
     */
    public $kpp;

    /**
     * @var string
     * @Assert\Length(
     *     min="8",
     *     max="8",
     *     exactMessage="ОКПО должен содержать 8 цифр"
     * )
     */
    public $okpo;

    /**
     * @var string
     * @Assert\Length(
     *     max="15",
     *     maxMessage="ОГРН должен содержать максимум 15 цифр"
     * )
     */
    public $ogrn;

    /**
     * @var bool
     */
    public $isNDS;

    /**
     * @var Address
     * @Assert\Valid()
     */
    public $address;

    public $phone;

    public $fax;

    /**
     * @var string
     * @Assert\Email()
     */
    public $email;

    /**
     * @var Bank
     * @Assert\Valid()
     */
    public $bank;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $rasschet;

    public function __construct(int $firmcontrID)
    {
        $this->firmcontrID = $firmcontrID;
    }

    public static function fromEntity(FirmContr $firmContr, TownFetcher $townFetcher, BankFetcher $bankFetcher): self
    {
        $command = new self($firmContr->getId());
        $command->organization = $firmContr->getUr()->getOrganization();
        $command->inn = $firmContr->getUr()->getInn();
        $command->kpp = $firmContr->getUr()->getKpp();
        $command->okpo = $firmContr->getUr()->getOkpo();
        $command->ogrn = $firmContr->getUr()->getOgrn();
        $command->isNDS = $firmContr->getUr()->isNDS();
        $command->address = new Address(
            new Town($firmContr->getTown()->getId(), $townFetcher->findTownsById($firmContr->getTown()->getId())->getTownFullName()),
            $firmContr->getAddress()->getZip(),
            $firmContr->getAddress()->getStreet(),
            $firmContr->getAddress()->getHouse(),
            $firmContr->getAddress()->getStr(),
            $firmContr->getAddress()->getKv()
        );
        $command->bank = new Bank($firmContr->getBank()->getId(), $bankFetcher->findBankById($firmContr->getBank()->getId())->getBankFullName());
        $command->phone = $firmContr->getPhone();
        $command->fax = $firmContr->getFax();
        $command->email = $firmContr->getEmail();
        $command->rasschet = $firmContr->getRasschet();
        return $command;
    }
}
