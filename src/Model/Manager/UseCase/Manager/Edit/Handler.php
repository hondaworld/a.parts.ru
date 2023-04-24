<?php

namespace App\Model\Manager\UseCase\Manager\Edit;

use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\Model\Manager\Entity\Manager\Email;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Manager\Entity\Manager\Name;
use App\Model\Manager\Entity\Type\ManagerTypeRepository;
use App\Model\Manager\Service\PasswordHasher;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Handler
{
    private $managers;
    private $flusher;
    private $hasher;
    private ManagerGroupRepository $groups;
    private ZapSkladRepository $sklads;
    private ManagerTypeRepository $types;
    private ParameterBagInterface $parameterBag;

    public function __construct(ManagerRepository $managers, ManagerGroupRepository  $groups, ZapSkladRepository $sklads, ManagerTypeRepository  $types, Flusher $flusher, PasswordHasher $hasher, ParameterBagInterface $parameterBag)
    {
        $this->managers = $managers;
        $this->flusher = $flusher;
        $this->hasher = $hasher;
        $this->groups = $groups;
        $this->sklads = $sklads;
        $this->types = $types;
        $this->parameterBag = $parameterBag;
    }

    public function checkLogin(Command $command): void
    {
        $manager = $this->managers->get($command->managerID);

        if ($this->managers->hasByLogin($command->login, $command->managerID)) {
            $command->photo = $manager->getPhoto() ? $this->parameterBag->get('manager_photo_www') . $manager->getPhoto() : '';
            throw new \DomainException('Менеджер с таким логином уже есть.');
        }
    }

    public function handle(Command $command): void
    {
        $manager = $this->managers->get($command->managerID);

        $manager->update(
            $command->login,
            $command->phonemob->phonemob ?: '',
            new Name(
                $command->firstname,
                $command->lastname,
                $command->middlename ?: ''
            ),
            $command->name,
            $command->nick,
            new Email($command->email ?: ''),
            $command->sex ?: "",
            $command->isHide,
            $command->isManager,
            $command->isAdmin,
            $command->dateofmanager ?: new \DateTime('0000-00-00'),
            $command->photo,
            $this->types->get($command->managerTypeID),
            $command->zp_spare,
            $command->zp_service
        );

        if ($command->password != '') $manager->changePasswordAdmin($this->hasher->hash($command->password));

        $manager->clearGroups();
        foreach ($command->groups as $groupID) {
            $group = $this->groups->get($groupID);
            $manager->assignGroup($group);
        }

        $manager->clearSklads();
        foreach ($command->sklads as $zapSkladID) {
            $sklad = $this->sklads->get($zapSkladID);
            $manager->assignSklad($sklad);
        }

        $this->flusher->flush();
    }
}
