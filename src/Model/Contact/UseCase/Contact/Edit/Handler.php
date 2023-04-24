<?php

namespace App\Model\Contact\UseCase\Contact\Edit;

use App\Model\Contact\Entity\Contact\Address;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Flusher;

class Handler
{
    private ContactRepository $contacts;
    private Flusher $flusher;
    private TownRepository $townRepository;

    public function __construct(ContactRepository $contacts, TownRepository $townRepository, Flusher $flusher)
    {
        $this->contacts = $contacts;
        $this->flusher = $flusher;
        $this->townRepository = $townRepository;
    }

    public function handle(Command $command): void
    {

        $contact = $this->contacts->get($command->contactID);

        if ($command->manager) {
            $command->isMain = $command->manager->checkIsMainContact($command->isMain, $contact);
        }

        if ($command->user) {
            $command->isMain = $command->user->checkIsMainContact($command->isMain, $contact);
        }

        if ($command->firm) {
            $command->isMain = $command->firm->checkIsMainContact($command->isMain, $contact);
        }

        $contact->update(
            $this->townRepository->get($command->address->town->id),
            new Address(
                $command->address->zip, $command->address->street, $command->address->house, $command->address->str, $command->address->kv
            ), $command->phone, $command->phonemob['phonemob'], $command->fax, $command->http, $command->email, $command->description, $command->isUr, $command->isMain
        );

        $this->flusher->flush();
    }
}
