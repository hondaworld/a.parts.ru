<?php

namespace App\Model\Manager\UseCase\Manager\Edit;

use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Contact\ContactFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $managerID;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="5",
     *     minMessage="Логин должен содержать не меньше 5 символов"
     * )
     */
    public $login;

    /**
     * @var string
     * @Assert\Length(
     *     min="6",
     *     minMessage="Пароль должен содержать не меньше 6 символов"
     * )
     */
    public $password;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $firstname;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $lastname;

    /**
     * @var string
     */
    public $middlename;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="3",
     *     maxMessage="Ник должен быть максимум 3 символа"
     * )
     */
    public $nick;

    /**
     * @var Phonemob
     * @Assert\Valid()
     */
    public $phonemob;

    /**
     * @var string
     * @Assert\Email()
     */
    public $email;

    /**
     * @Assert\Choice({"", "M", "F"})
     */
    public $sex;

    /**
     * @var string
     */
    public $photo;

    public $isHide;

    public $isManager;

    public $isAdmin;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofmanager;

    /**
     * @var array
     */
    public $groups;

    /**
     * @var array
     */
    public $sklads;

    public $managerTypeID;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $zp_spare;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $zp_service;

    public function __construct(int $managerID)
    {
        $this->managerID = $managerID;
    }

    public static function fromManager(Manager $manager, string $photoDirectory): self
    {
        $command = new self($manager->getId());
        $command->login = $manager->getLogin();
        $command->name = $manager->getName();
        $command->nick = $manager->getNick();
        $command->firstname = $manager->getManagerName()->getFirstname();
        $command->lastname = $manager->getManagerName()->getLastname();
        $command->middlename = $manager->getManagerName()->getMiddlename();
        $command->sex = $manager->getSex();
        $command->isHide = $manager->getIsHide();
        $command->isManager = $manager->getIsManager();
        $command->isAdmin = $manager->getIsAdmin();
        $command->dateofmanager = $manager->getDateofmanger();
        $command->zp_spare = $manager->getZpSpare();
        $command->zp_service = $manager->getZpService();
        $command->photo = $manager->getPhoto() ? $photoDirectory . $manager->getPhoto() : '';
        $command->phonemob = new Phonemob($manager->getPhonemob());
        $command->email = $manager->getEmail()->getValue();
        $command->groups = array_map(function (ManagerGroup $group): int {
            return $group->getId();
        }, $manager->getGroups());
        $command->sklads = array_map(function (ZapSklad $sklad): int {
            return $sklad->getId();
        }, $manager->getSklads());
        $command->managerTypeID = $manager->getType()->getId();
        return $command;
    }
}
