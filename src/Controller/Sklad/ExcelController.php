<?php

namespace App\Controller\Sklad;

use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\Sklad\Service\ExcelHelper;
use App\Model\Sklad\Service\ExcelHelperSummary;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/price-list/excel", name="price.list.excel")
 */
class ExcelController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param OptFetcher $fetcher
     * @param ExcelHelper $excelHelper
     * @return Response
     */
    public function index(OptFetcher $fetcher, ExcelHelper $excelHelper): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PriceListExcel');

        $opts = $fetcher->allNotHide();

        $files = [];
        try {
            $files = $excelHelper->getAllFilesFromDir();
        } catch (DomainException | Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('app/sklads/excel/index.html.twig', [
            'opts' => $opts,
            'files' => $files,
            'fileDir' => '/' . $this->getParameter('price_directory_www') . 'email/'
        ]);
    }

    /**
     * @Route("/excel", name=".excel")
     * @param Request $request
     * @param ExcelHelper $excelHelper
     * @param OptRepository $optRepository
     * @param ZapSkladRepository $zapSkladRepository
     * @return Response
     */
    public function excel(Request $request, ExcelHelper $excelHelper, OptRepository $optRepository, ZapSkladRepository $zapSkladRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PriceListExcel');

        $optID = $request->query->getInt('optID');
        $zapSkladID = $request->query->getInt('zapSkladID');
        $isSimple = $request->query->getBoolean('isSimple', false);

        try {
            $opt = $optRepository->get($optID);
            $zapSklad = $zapSkladID ? $zapSkladRepository->get($zapSkladID) : null;
            $excelHelper->saveAndGet($opt, $zapSklad, $isSimple);

        } catch (DomainException | Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('price.list.excel');
    }

    /**
     * @Route("/summary", name=".summary")
     * @param Request $request
     * @param ExcelHelperSummary $excelHelperSummary
     * @param OptRepository $optRepository
     * @return Response
     */
    public function summary(Request $request, ExcelHelperSummary $excelHelperSummary, OptRepository $optRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PriceListExcel');

        $optID = $request->query->getInt('optID');

        try {
            $opt = $optRepository->get($optID);
            $excelHelperSummary->saveAndGet($opt);

        } catch (DomainException | Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

//        return $this->render('app/home.html.twig', ['news' => []]);
        return $this->redirectToRoute('price.list.excel');
    }
}
