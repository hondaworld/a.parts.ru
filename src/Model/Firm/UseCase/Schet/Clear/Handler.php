<?php

namespace App\Model\Firm\UseCase\Schet\Clear;

use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;
use App\Model\User\Entity\User\User;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;

    public function __construct(
        SchetRepository       $schetRepository,
        Flusher               $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
    }

    public function handle(User $user): void
    {
        $schet = $this->schetRepository->findNewByUser($user);
        if ($schet) {
            $schet->clearOrderGoods();
        }

        $this->flusher->flush();
    }
}
