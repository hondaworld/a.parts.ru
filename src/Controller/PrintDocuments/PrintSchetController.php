<?php

namespace App\Controller\PrintDocuments;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Firm\Entity\Schet\SchetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="")
 */
class PrintSchetController extends AbstractController
{
    /**
     * @Route("/schet.php", name="schet.php")
     * @param Request $request
     * @param SchetRepository $schetRepository
     * @param ZapCardRepository $zapCardRepository
     * @param NalogNdsRepository $nalogNdsRepository
     * @return Response
     */
    public function schet(
        Request            $request,
        SchetRepository    $schetRepository,
        ZapCardRepository  $zapCardRepository,
        NalogNdsRepository $nalogNdsRepository
    ): Response
    {
        $schetID = $request->query->getInt('id');
        $schet = $schetRepository->get($schetID);

        $document_num = $schet->getDocument()->getDocumentNum();;
        $document_date = $schet->getDateofadded();

        $nalogNds = $nalogNdsRepository->getLastByFirm($schet->getFirm(), $schet->getDateofadded());

        $sum = 0;
        $sumNds = 0;
        $zapCards = [];
        $schetGoods = $schet->getSchetGoods();
        foreach ($schetGoods as $item) {
            $zapCards[$item->getId()] = $zapCardRepository->getByNumberAndCreaterID($item->getNumber()->getValue(), $item->getCreater()->getId());

            $price = $item->getPrice() * $item->getQuantity();
            $sum += $price;
            $sumNds += $price / (100 + $nalogNds->getNds()) * $nalogNds->getNds();
        }

        return $this->render('app/firms/schet/print.html.twig', [
            'schet' => $schet,
            'sum' => $sum,
            'sumNds' => $sumNds,
            'nds' => $nalogNds->getNds(),
            'zapCards' => $zapCards,
            'document_num' => $document_num,
            'document_date' => $document_date
        ]);
    }

}
