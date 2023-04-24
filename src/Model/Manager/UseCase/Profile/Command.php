<?php

namespace App\Model\Manager\UseCase\Profile;

use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Contact\TownFetcher;
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
     * @Assert\NotBlank()
     * @Assert\Choice({"M", "F"})
     */
    public $sex;

    /**
     * @var string
     */
    public $photo;

    public $isHide;

    public $isAdmin;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofmanager;

    public $description;

    /**
     * @Assert\Valid()
     */
    public $town;

    public function __construct(string $managerID)
    {
        $this->managerID = $managerID;
    }

    public static function fromManager(Manager $manager, TownFetcher $fetcher, string $photoDirectory): self
    {
        $command = new self($manager->getId());
        $command->login = $manager->getLogin();
        $command->name = $manager->getName();
        $command->firstname = $manager->getManagerName()->getFirstname();
        $command->lastname = $manager->getManagerName()->getLastname();
        $command->middlename = $manager->getManagerName()->getMiddlename();
        $command->sex = $manager->getSex();
        $command->isHide = $manager->getIsHide();
        $command->isAdmin = $manager->getIsAdmin();
        $command->dateofmanager = $manager->getDateofmanger();
        $command->photo = $manager->getPhoto() ? $photoDirectory . $manager->getPhoto() : '';
        $command->phonemob = new Phonemob($manager->getPhonemob());
        $command->email = $manager->getEmail()->getValue();
        $command->town = new Town(598, $fetcher->findTownsById(598)->getTownFullName());
        return $command;
    }
}
