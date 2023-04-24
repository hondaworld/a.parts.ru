<?php

namespace App\Model\User\UseCase\FirmContr\Create;

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

    public function __construct()
    {
        $this->address = new Address(new Town());
        $this->bank = new Bank();
    }
}
