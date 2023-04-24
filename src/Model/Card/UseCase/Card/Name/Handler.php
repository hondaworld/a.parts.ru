<?php

namespace App\Model\Card\UseCase\Card\Name;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Group\ZapGroupRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;
    private ZapGroupRepository $zapGroupRepository;

    public function __construct(
        ZapCardRepository $repository,
        ZapGroupRepository $zapGroupRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->zapGroupRepository = $zapGroupRepository;
    }

    public function handle(Command $command): void
    {
        if (!$command->zapGroupID && !$command->name_big) {
            throw new \DomainException('Группа товаров должна быть выбрана или заполнено альтернативное наименование');
        }

        $zapCard = $this->repository->get($command->zapCardID);

        $zapCard->updateName(
            $command->zapGroupID ? $this->zapGroupRepository->get($command->zapGroupID) : null,
            $command->name,
            $command->description,
            $command->name_big,
            $command->nameEng
        );

        $this->flusher->flush();
    }
}
