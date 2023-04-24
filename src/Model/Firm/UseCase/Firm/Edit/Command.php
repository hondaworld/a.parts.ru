<?php

namespace App\Model\Firm\UseCase\Firm\Edit;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $firmID;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="50",
     *     minMessage="Краткое наименование должно быть меньше 50 символов"
     * )
     */
    public $name_short;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     * @Assert\Length(
     *     min="10",
     *     max="12",
     *     minMessage="ИНН должен содержать 10 или 12 цифр",
     *     maxMessage="ИНН должен содержать 10 или 12 цифр"
     * )
     */

    public $inn;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     * @Assert\Length(
     *     min="9",
     *     max="9",
     *     exactMessage="КПП должен содержать 9 цифр"
     * )
     */
    public $kpp;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     * @Assert\Length(
     *     min="8",
     *     max="8",
     *     exactMessage="ОКПО должен содержать 8 цифр"
     * )
     */
    public $okpo;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
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
     * @var bool
     */
    public $isUr;

    /**
     * @var int
     */
    public $nalogID;

    /**
     * @var int
     */
    public $directorID;

    /**
     * @var int
     */
    public $buhgalterID;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofclosed;

    public function __construct(int $firmID)
    {
        $this->firmID = $firmID;
    }

    public static function fromEntity(Firm $firm): self
    {
        $command = new self($firm->getId());
        $command->name_short = $firm->getNameShort();
        $command->name = $firm->getName();
        $command->inn = $firm->getInn();
        $command->kpp = $firm->getKpp();
        $command->okpo = $firm->getOkpo();
        $command->ogrn = $firm->getOgrn();
        $command->isNDS = $firm->isNDS();
        $command->isUr = $firm->isUr();
        $command->nalogID = $firm->getNalog()->getId();
        $command->directorID = $firm->getDirector() ? $firm->getDirector()->getId() : null;
        $command->buhgalterID = $firm->getBuhgalter() ? $firm->getBuhgalter()->getId() : null;
        $command->dateofadded = $firm->getDateofadded();
        $command->dateofclosed = $firm->getDateofclosed();
        return $command;
    }
}
