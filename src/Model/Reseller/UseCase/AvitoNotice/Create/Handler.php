<?php

namespace App\Model\Reseller\UseCase\AvitoNotice\Create;

use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Flusher;
use App\Model\Reseller\Entity\Avito\AvitoNotice;
use App\Model\Reseller\Entity\Avito\AvitoNoticeRepository;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;

class Handler
{
    private Flusher $flusher;
    private AvitoNoticeRepository $avitoNoticeRepository;
    private ZapCardPriceService $zapCardPriceService;
    private OptRepository $optRepository;

    public function __construct(AvitoNoticeRepository $avitoNoticeRepository, ZapCardPriceService $zapCardPriceService, OptRepository $optRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->avitoNoticeRepository = $avitoNoticeRepository;
        $this->zapCardPriceService = $zapCardPriceService;
        $this->optRepository = $optRepository;
    }

    public function handle(Command $command, string $zap_card_photo_folder): AvitoNotice
    {
        if ($this->avitoNoticeRepository->hasByZapCard($command->zapCard)) {
            throw new \DomainException('Объявление для этой детали уже есть');
        }

        $image_urls = [];

        $zapCard = $command->zapCard;

        $title = ($zapCard->getZapGroup() ? $zapCard->getZapGroup()->getName() : '') . ' ' . $zapCard->getNumber()->getValue() . ' ' . $zapCard->getCreater()->getName();
//        $price = $this->zapCardPriceService->priceOpt($zapCard, $this->optRepository->get(Opt::DEFAULT_OPT_ID));

        foreach ($zapCard->getSortedPhotos() as $photo) {
            $image_urls[] = $zap_card_photo_folder . $photo->getBimage();
        }

        $avitoNotice = new AvitoNotice(
            $command->zapCard,
            AvitoNotice::CONTACT_PHONE,
            AvitoNotice::ADDRESS,
            $title,
            $zapCard->getCreater()->getNameAvito(),
            $zapCard->getNumber()->getValue(),
            implode(' | ', $image_urls)
        );

        $this->avitoNoticeRepository->add($avitoNotice);
        $this->flusher->flush();
        return $avitoNotice;

    }
}
