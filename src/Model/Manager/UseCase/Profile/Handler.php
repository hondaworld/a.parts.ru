<?php

namespace App\Model\Manager\UseCase\Profile;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Email;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Manager\Entity\Manager\Name;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Handler
{
    private $managers;
    private $flusher;
    private $parameterBag;

    public function __construct(ManagerRepository $managers, ParameterBagInterface $parameterBag, Flusher $flusher)
    {
        $this->managers = $managers;
        $this->flusher = $flusher;
        $this->parameterBag = $parameterBag;
    }

    public function handle(Command $command): void
    {
        $manager = $this->managers->get($command->managerID);

        if ($this->managers->hasByLogin($command->login, $command->managerID)) {
            $command->photo = $manager->getPhoto() ? $this->parameterBag->get('manager_photo_www') . $manager->getPhoto() : '';
            throw new \DomainException('Менеджер с таким логином уже есть.');
        }

        $manager->updateProfile(
            $command->login,
            $command->phonemob->phonemob ?: '',
            new Name(
                $command->firstname,
                $command->lastname,
                $command->middlename ?: ''
            ),
            $command->name,
            new Email($command->email ?: ''),
            $command->sex ?: "",
            $command->dateofmanager ?: new \DateTime('0000-00-00'),
            $command->photo
        );


        $this->flusher->flush();
    }
}
