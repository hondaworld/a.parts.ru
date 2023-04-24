<?php

namespace App\Model\Order\UseCase\Site\Edit;

use App\Model\Auto\Entity\Marka\AutoMarkaRepository;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Order\Entity\Site\SiteRepository;

class Handler
{
    private SiteRepository $siteRepository;
    private Flusher $flusher;
    private CreaterRepository $createrRepository;
    private AutoMarkaRepository $autoMarkaRepository;

    public function __construct(SiteRepository $siteRepository, CreaterRepository $createrRepository, AutoMarkaRepository $autoMarkaRepository, Flusher $flusher)
    {
        $this->siteRepository = $siteRepository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->autoMarkaRepository = $autoMarkaRepository;
    }

    public function handle(Command $command): void
    {
        $site = $this->siteRepository->get($command->siteID);

        $site->update(
            $command->name_short,
            $command->name,
            $command->url,
            $command->isSklad,
            $command->norma_price
        );

        $site->clearCreaters();
        foreach ($command->creaters as $createrID) {
            $creater = $this->createrRepository->get($createrID);
            $site->assignCreater($creater);
        }

        $site->clearAutoMarka();
        foreach ($command->auto_marka as $auto_markaID) {
            $autoMarka = $this->autoMarkaRepository->get($auto_markaID);
            $site->assignAutoMarka($autoMarka);
        }

        $this->flusher->flush();
    }
}
