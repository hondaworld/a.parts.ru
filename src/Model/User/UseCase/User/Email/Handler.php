<?php

namespace App\Model\User\UseCase\User\Email;

use App\Model\Flusher;
use App\Model\User\Entity\EmailStatus\UserEmailStatusRepository;
use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\User\UserEmailStatusFetcher;

class Handler
{
    private $users;
    private $flusher;
    private $userEmailStatusRepository;

    public function __construct(UserRepository $users, Flusher $flusher, UserEmailStatusRepository $userEmailStatusRepository)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->userEmailStatusRepository = $userEmailStatusRepository;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $email = new Email($command->email ?: '', $command->isNotification, $command->isActive);

        $statuses = [];
        foreach ($this->userEmailStatusRepository->findAll() as $userEmailStatus) {
            if (!in_array($userEmailStatus->getId(), $command->excludeEmailStatuses)) $statuses[] = $userEmailStatus;
        }

        $user->updateEmail($email, $statuses);

        $this->flusher->flush();
    }
}
