<?php

namespace App\Model\Firm\UseCase\Schet\UserContact;

use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;
    private ContactRepository $contactRepository;

    public function __construct(
        SchetRepository   $schetRepository,
        ContactRepository $contactRepository,
        Flusher           $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
        $this->contactRepository = $contactRepository;
    }

    public function handle(Command $command): void
    {
        $schet = $this->schetRepository->get($command->schetID);
        $schet->updateUserContact($this->contactRepository->get($command->exp_user_contactID));
        $this->flusher->flush();
    }
}
