<?php

namespace App\Model\Work\UseCase\Group\Auto;

use App\Model\Flusher;
use App\Model\Provider\Entity\Opt\ProviderPriceOpt;
use App\Model\Provider\Entity\Opt\ProviderPriceOptRepository;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\Model\Sklad\Entity\Opt\PriceListOpt;
use App\Model\Sklad\Entity\Opt\PriceListOptRepository;
use App\Model\Sklad\Entity\PriceList\PriceListRepository;
use App\Model\Work\Entity\Group\WorkGroupRepository;
use App\Model\Work\Entity\Link\LinkWorkAuto;
use App\Model\Work\Entity\Link\LinkWorkAutoRepository;
use App\Model\Work\Entity\Link\LinkWorkNormaAuto;
use App\Model\Work\Entity\Link\LinkWorkNormaAutoRepository;
use App\Model\Work\Entity\Link\LinkWorkPartsAuto;
use App\Model\Work\Entity\Link\LinkWorkPartsAutoRepository;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Provider\ProviderPriceOptFetcher;
use App\ReadModel\Sklad\PriceListOptFetcher;
use App\ReadModel\User\OptFetcher;

class Handler
{
    private $linkWorkAutoRepository;
    private $flusher;
    private $priceListOptRepository;
    private $priceListOptFetcher;
    private $optFetcher;
    private WorkGroupRepository $workGroupRepository;
    private LinkWorkNormaAutoRepository $linkWorkNormaAutoRepository;
    private LinkWorkPartsAutoRepository $linkWorkPartsAutoRepository;

    public function __construct(WorkGroupRepository $workGroupRepository, LinkWorkAutoRepository $linkWorkAutoRepository, LinkWorkNormaAutoRepository $linkWorkNormaAutoRepository, LinkWorkPartsAutoRepository $linkWorkPartsAutoRepository, Flusher $flusher)
    {
        $this->linkWorkAutoRepository = $linkWorkAutoRepository;
        $this->flusher = $flusher;
        $this->workGroupRepository = $workGroupRepository;
        $this->linkWorkNormaAutoRepository = $linkWorkNormaAutoRepository;
        $this->linkWorkPartsAutoRepository = $linkWorkPartsAutoRepository;
    }

    public function handle(Command $command): void
    {
        $workGroup = $this->workGroupRepository->get($command->workGroupID);

        $this->linkWorkAutoRepository->deleteByWorkGroupAndAutoMarka($workGroup, $command->autoMarka);
        if ($command->linkMarka) {
            $linkWorkAuto = new LinkWorkAuto($workGroup, $command->autoMarka, null, null, null);
            $this->linkWorkAutoRepository->add($linkWorkAuto);
        }

        $this->linkWorkNormaAutoRepository->deleteByWorkGroupAndAutoMarka($workGroup, $command->autoMarka);
        if ($command->normaMarka) {
            $linkWorkAuto = new LinkWorkNormaAuto($workGroup, $command->autoMarka, null, null, null, $command->normaMarka);
            $this->linkWorkNormaAutoRepository->add($linkWorkAuto);
        }

        $this->linkWorkPartsAutoRepository->deleteByWorkGroupAndAutoMarka($workGroup, $command->autoMarka);
        if ($command->partsMarka) {
            $linkWorkAuto = new LinkWorkPartsAuto($workGroup, $command->autoMarka, null, null, null, $command->partsMarka);
            $this->linkWorkPartsAutoRepository->add($linkWorkAuto);
        }

        foreach ($command->autoMarka->getModels() as $model) {
            $this->linkWorkAutoRepository->deleteByWorkGroupAndAutoModel($workGroup, $model);
            if ($command->linkModel[$model->getId()]) {
                $linkWorkAuto = new LinkWorkAuto($workGroup, null, $model, null, null);
                $this->linkWorkAutoRepository->add($linkWorkAuto);
            }
            $this->linkWorkNormaAutoRepository->deleteByWorkGroupAndAutoModel($workGroup, $model);
            if ($command->normaModel[$model->getId()]) {
                $linkWorkAuto = new LinkWorkNormaAuto($workGroup, null, $model, null, null, $command->normaModel[$model->getId()]);
                $this->linkWorkNormaAutoRepository->add($linkWorkAuto);
            }
            $this->linkWorkPartsAutoRepository->deleteByWorkGroupAndAutoModel($workGroup, $model);
            if ($command->partsModel[$model->getId()]) {
                $linkWorkAuto = new LinkWorkPartsAuto($workGroup, null, $model, null, null, $command->partsModel[$model->getId()]);
                $this->linkWorkPartsAutoRepository->add($linkWorkAuto);
            }

            foreach ($model->getGenerations() as $generation) {
                $this->linkWorkAutoRepository->deleteByWorkGroupAndAutoGeneration($workGroup, $generation);
                if ($command->linkGeneration[$generation->getId()]) {
                    $linkWorkAuto = new LinkWorkAuto($workGroup, null, null, $generation, null);
                    $this->linkWorkAutoRepository->add($linkWorkAuto);
                }
                $this->linkWorkNormaAutoRepository->deleteByWorkGroupAndAutoGeneration($workGroup, $generation);
                if ($command->normaGeneration[$generation->getId()]) {
                    $linkWorkAuto = new LinkWorkNormaAuto($workGroup, null, null, $generation, null, $command->normaGeneration[$generation->getId()]);
                    $this->linkWorkNormaAutoRepository->add($linkWorkAuto);
                }
                $this->linkWorkPartsAutoRepository->deleteByWorkGroupAndAutoGeneration($workGroup, $generation);
                if ($command->partsGeneration[$generation->getId()]) {
                    $linkWorkAuto = new LinkWorkPartsAuto($workGroup, null, null, $generation, null, $command->partsGeneration[$generation->getId()]);
                    $this->linkWorkPartsAutoRepository->add($linkWorkAuto);
                }

                foreach ($generation->getModifications() as $modification) {
                    $this->linkWorkAutoRepository->deleteByWorkGroupAndAutoModification($workGroup, $modification);
                    if ($command->linkModification[$modification->getId()]) {
                        $linkWorkAuto = new LinkWorkAuto($workGroup, null, null, null, $modification);
                        $this->linkWorkAutoRepository->add($linkWorkAuto);
                    }
                    $this->linkWorkNormaAutoRepository->deleteByWorkGroupAndAutoModification($workGroup, $modification);
                    if ($command->normaModification[$modification->getId()]) {
                        $linkWorkAuto = new LinkWorkNormaAuto($workGroup, null, null, null, $modification, $command->normaModification[$modification->getId()]);
                        $this->linkWorkNormaAutoRepository->add($linkWorkAuto);
                    }
                    $this->linkWorkPartsAutoRepository->deleteByWorkGroupAndAutoModification($workGroup, $modification);
                    if ($command->partsModification[$modification->getId()]) {
                        $linkWorkAuto = new LinkWorkPartsAuto($workGroup, null, null, null, $modification, $command->partsModification[$modification->getId()]);
                        $this->linkWorkPartsAutoRepository->add($linkWorkAuto);
                    }
                }
            }
        }


//        dump($command);
//        $priceList = $this->priceListRepository->get($command->price_listID);
//        $opts = $this->optFetcher->assoc();
//
//        $this->priceListOptFetcher->deleteByPriceList($priceList);
//
//        foreach ($opts as $optID => $opt) {
//            $profit = $command->{'profit_' . $optID};
//            $profit = str_replace(',', '.', $profit);
//            if ($profit && floatval($profit) > 0) {
//                $providerPriceOpt = new PriceListOpt(
//                    $priceList,
//                    $this->optFetcher->get($optID),
//                    $profit
//                );
//                $this->priceListOptRepository->add($providerPriceOpt);
//            }
//        }
//
        $this->flusher->flush();
    }
}
