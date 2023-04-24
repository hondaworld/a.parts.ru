<?php


namespace App\Controller\Provider;


use App\ReadModel\Provider\LogFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/providers/prices/upload/logs", name="providers.prices.upload.logs")
 */
class LogsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param LogFetcher $fetcher
     * @return Response
     */
    public function index(Request $request, LogFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ProviderPriceUpload');

        $all = $fetcher->all();

        return $this->render('app/providers/prices/upload/log.html.twig', [
            'all' => $all,
        ]);
    }

    /**
     * @Route("/all/", name=".all")
     * @param Request $request
     * @param LogFetcher $fetcher
     * @return Response
     */
    public function full(Request $request, LogFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ProviderPriceUpload');

        $all = $fetcher->full();

        return $this->render('app/providers/prices/upload/logAll.html.twig', [
            'all' => $all,
        ]);
    }
}