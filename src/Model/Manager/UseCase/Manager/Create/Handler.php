<?php

namespace App\Model\Manager\UseCase\Manager\Create;

use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Manager\Entity\Manager\Name;
use App\Model\Manager\Entity\Type\ManagerType;
use App\Model\Manager\Entity\Type\ManagerTypeRepository;
use App\Model\Manager\Service\PasswordHasher;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Handler
{
    private $managers;
    private $flusher;
    private $hasher;
    private ManagerGroupRepository $groups;
    private ManagerTypeRepository $types;

    public function __construct(ManagerRepository $managers, ManagerGroupRepository  $groups, ManagerTypeRepository  $types, Flusher $flusher, PasswordHasher $hasher)
    {
        $this->managers = $managers;
        $this->flusher = $flusher;
        $this->hasher = $hasher;
        $this->groups = $groups;
        $this->types = $types;
    }

    public function handle(Command $command): void
    {

        if ($this->managers->hasByLogin($command->login)) {
            throw new \DomainException('Менеджер с таким логином уже есть.');
        }

        $name = new Name(
            $command->firstname,
            $command->lastname,
            $command->middlename ?: ''
        );

        $manager = new Manager(
            $command->login,
            $this->hasher->hash($command->password),
            $name,
            $name->generateName(),
            $this->types->get(ManagerType::DEFAULT_ID)
        );

        foreach ($command->groups as $groupID) {
            $group = $this->groups->get($groupID);
            $manager->assignGroup($group);
        }

        $this->managers->add($manager);

        $this->flusher->flush();
    }
}
