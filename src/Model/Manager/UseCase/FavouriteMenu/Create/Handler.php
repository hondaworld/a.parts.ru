<?php

namespace App\Model\Manager\UseCase\FavouriteMenu\Create;

use App\Model\Flusher;
use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu;
use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenuRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Menu\Entity\Section\MenuSectionRepository;

class Handler
{
    private FavouriteMenuRepository $favouriteMenuRepository;
    private Flusher $flusher;
    private MenuSectionRepository $menuSectionRepository;

    public function __construct(FavouriteMenuRepository $favouriteMenuRepository, MenuSectionRepository $menuSectionRepository, Flusher $flusher)
    {
        $this->favouriteMenuRepository = $favouriteMenuRepository;
        $this->flusher = $flusher;
        $this->menuSectionRepository = $menuSectionRepository;
    }

    public function handle(Command $command, Manager $manager): void
    {
        if (!$command->url && !$command->menu_section_id) {
            throw new \DomainException('Задайте адрес страницы или выберите пункт меню');
        }

        $menuSection = $command->menu_section_id ? $this->menuSectionRepository->get($command->menu_section_id->item['id']) : null;

        $favouriteMenu = new FavouriteMenu($manager, $command->name, $menuSection, $command->url, $this->favouriteMenuRepository->getNextSort($manager));
        $this->favouriteMenuRepository->add($favouriteMenu);

        $this->flusher->flush();
    }
}
