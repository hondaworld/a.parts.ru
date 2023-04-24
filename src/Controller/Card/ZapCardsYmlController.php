<?php

namespace App\Controller\Card;

use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Card\Filter;
use App\ReadModel\Card\ZapCardPhotoFetcher;
use App\ReadModel\Shop\ShopTypeFetcher;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/yml", name="yml")
 */
class ZapCardsYmlController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ShopTypeFetcher $shopTypeFetcher
     * @param ZapCardPhotoFetcher $zapCardPhotoFetcher
     * @param OptRepository $optRepository
     * @param PartPriceService $partPriceService
     * @return Response
     * @throws Exception
     */
    public function yml(ShopTypeFetcher $shopTypeFetcher, ZapCardPhotoFetcher $zapCardPhotoFetcher, OptRepository $optRepository, PartPriceService $partPriceService): Response
    {
        $types = $shopTypeFetcher->assoc();
        $parts = $partPriceService->hondaInWarehouse($optRepository->get(Opt::DEFAULT_OPT_ID), null);
        $photos = $zapCardPhotoFetcher->allByZapCards(array_map(function ($item) {return $item['zapCardID'];}, $parts));

//        return $this->render('app/home.html.twig', ['news' => []]);

        $xml = $this->renderView('app/card/yml/index.xml.twig', [
            'types' => $types,
            'parts' => $parts,
            'photos' => $photos,
            'zap_card_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('zap_card_photo') . '/'
        ]);

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}
