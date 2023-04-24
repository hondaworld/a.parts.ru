<?php

namespace App\Model\Manager\UseCase\FavouriteMenu\Edit;

use App\Model\Flusher;
use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenuRepository;

class Handler
{
    private FavouriteMenuRepository $favouriteMenuRepository;
    private Flusher $flusher;

    public function __construct(FavouriteMenuRepository $favouriteMenuRepository, Flusher $flusher)
    {
        $this->favouriteMenuRepository = $favouriteMenuRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $favouriteMenu = $this->favouriteMenuRepository->get($command->id);
        $favouriteMenu->update($command->name);
        $this->flusher->flush();
    }
}
