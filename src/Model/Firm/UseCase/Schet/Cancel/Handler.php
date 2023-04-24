<?php

namespace App\Model\Firm\UseCase\Schet\Cancel;

use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;

    public function __construct(
        SchetRepository          $schetRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
    }

    public function handle(Command $command): void
    {
        $schet = $this->schetRepository->get($command->schetID);

        if (!$schet->isCancelAllow()) {
            throw new \DomainException('Статус должен быть "Ожидает оплаты"');
        }

        $schet->clearOrderGoods();
        $schet->cancel($command->cancelReason);
        $this->flusher->flush();
    }
}
