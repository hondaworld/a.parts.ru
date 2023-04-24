<?php

namespace App\Model\User\UseCase\User\Ur;

use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $organization;

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
     * @var string
     */
    public $dogovor_num;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dogovor_date;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->name = $user->getName();
        $command->organization = $user->getUr()->getOrganization();
        $command->inn = $user->getUr()->getInn();
        $command->kpp = $user->getUr()->getKpp();
        $command->okpo = $user->getUr()->getOkpo();
        $command->ogrn = $user->getUr()->getOgrn();
        $command->dogovor_num = $user->getUr()->getDogovorNum();
        $command->dogovor_date = $user->getUr()->getDogovorDate();
        $command->isNDS = $user->getUr()->isNDS();
        $command->isUr = $user->getUr()->isUr();
        return $command;
    }
}
