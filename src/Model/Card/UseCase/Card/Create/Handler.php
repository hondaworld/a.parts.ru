<?php

namespace App\Model\Card\UseCase\Card\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Group\ZapGroupRepository;
use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Card\Entity\Measure\EdIzmRepository;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Shop\Entity\ShopType\ShopTypeRepository;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private ShopTypeRepository $shopTypeRepository;
    private ZapGroupRepository $zapGroupRepository;
    private PriceGroupRepository $priceGroupRepository;
    private EdIzmRepository $edIzmRepository;

    public function __construct(
        ZapCardRepository $repository,
        CreaterRepository $createrRepository,
        ShopTypeRepository $shopTypeRepository,
        ZapGroupRepository $zapGroupRepository,
        PriceGroupRepository $priceGroupRepository,
        EdIzmRepository $edIzmRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->shopTypeRepository = $shopTypeRepository;
        $this->zapGroupRepository = $zapGroupRepository;
        $this->priceGroupRepository = $priceGroupRepository;
        $this->edIzmRepository = $edIzmRepository;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);
        if ($this->repository->hasByNumber($number, $this->createrRepository->get($command->createrID))) {
            throw new \DomainException('Такой номер уже есть');
        }

        $zapCard = new ZapCard(
            $number,
            $this->createrRepository->get($command->createrID),
            $this->shopTypeRepository->get($command->shop_typeID),
            $this->zapGroupRepository->get($command->zapGroupID),
            $command->name,
            $command->description,
            $this->priceGroupRepository->get($command->price_groupID),
            $this->edIzmRepository->get(EdIzm::DEFAULT_ID)
        );

        $this->repository->add($zapCard);

        $this->flusher->flush();
    }
}
