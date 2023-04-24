<?php

namespace App\Model\User\UseCase\User\Price;

use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\User\Price;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $createrRepository;
    private $zapSkladRepository;
    private $flusher;

    public function __construct(UserRepository $users, CreaterRepository $createrRepository, ZapSkladRepository $zapSkladRepository, Flusher $flusher)
    {
        $this->users = $users;
        $this->createrRepository = $createrRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $price = new Price(
            $command->email,
            $command->email_send,
            $command->filename,
            $command->first_line,
            $command->line,
            $command->order_num,
            $command->number_num,
            $command->creater_num,
            $command->quantity_num,
            $command->price_num
        );

        $user->updatePrice(
            $price,
            $command->createrID ? $this->createrRepository->get($command->createrID) : null,
            $command->zapSkladID ? $this->zapSkladRepository->get($command->zapSkladID) : null
        );

        $this->flusher->flush();
    }
}
