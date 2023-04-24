<?php

namespace App\Model\Contact\UseCase\Contact\Create;

use App\Model\Contact\Entity\Contact\Address;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private TownRepository $townRepository;

    public function __construct(TownRepository $townRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->townRepository = $townRepository;
    }

    public function handle(Command $command): void
    {
        $object = null;
        if ($command->manager) {
            $command->isMain = $command->manager->checkIsMainContact($command->isMain);
            $object = $command->manager;
        }

        if ($command->user) {
            $command->isMain = $command->user->checkIsMainContact($command->isMain);
            $object = $command->user;
        }

        if ($command->firm) {
            $command->isMain = $command->firm->checkIsMainContact($command->isMain);
            $object = $command->firm;
        }

        $contact = new Contact(
            $object,
            $this->townRepository->get($command->address->town->id),
            new Address(
                $command->address->zip, $command->address->street, $command->address->house, $command->address->str, $command->address->kv
            ), $command->phone, $command->phonemob['phonemob'], $command->fax, $command->http, $command->email, $command->description, $command->isUr, $command->isMain
        );

        $object->assignContact($contact);

        $this->flusher->flush();
    }
}
