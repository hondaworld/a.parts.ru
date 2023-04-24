<?php

namespace App\Model\Manager\UseCase\Password;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Manager\Service\PasswordHasher;

class Handler
{
    private $managers;
    private $flusher;
    private $hasher;

    public function __construct(ManagerRepository $managers, Flusher $flusher, PasswordHasher $hasher)
    {
        $this->managers = $managers;
        $this->flusher = $flusher;
        $this->hasher = $hasher;
    }

    public function handle(Command $command): void
    {
        $manager = $this->managers->get($command->managerID);

        if (!$this->hasher->validate($command->password, $manager->getPasswordAdmin())) {
            throw new \DomainException('Пароль введен неверно');
        }

        if ($command->password_new != $command->password_confirm) {
            throw new \DomainException('Пароли не совпадают');
        }

        $manager->changePasswordAdmin($this->hasher->hash($command->password_new));

        $this->flusher->flush();
    }
}
