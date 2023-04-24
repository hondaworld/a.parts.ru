<?php

namespace App\Model\Income\UseCase\Income\Gtd;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use App\Model\Shop\Entity\Gtd\ShopGtdRepository;

class Handler
{
    private $repository;
    private $flusher;
    private ShopGtdRepository $shopGtdRepository;

    public function __construct(
        IncomeRepository $repository,
        ShopGtdRepository $shopGtdRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->shopGtdRepository = $shopGtdRepository;
    }

    public function handle(Command $command): void
    {
        $income = $this->repository->get($command->incomeID);

        $gtd = new Gtd($command->gtd);

        if ($gtd->getValue() != '') {
            $shopGtd = $this->shopGtdRepository->findOneBy(['name' => $gtd]);
            if (!$shopGtd) {
                $shopGtd = new ShopGtd($gtd);
                $this->shopGtdRepository->add($shopGtd);
            }
        } else {
            $shopGtd = null;
        }

        $income->updateGtd($shopGtd);

        $this->flusher->flush();
    }
}
