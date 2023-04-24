<?php

namespace App\Controller\Analytics;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Analytics\Filter;
use App\ReadModel\Analytics\AnalyticsOrderRegionFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/analytics/orderRegions", name="analytics.orderRegions")
 */
class AnalyticsOrderRegionsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param AnalyticsOrderRegionFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, AnalyticsOrderRegionFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsOrderRegion');

        $settings = $settings->get('analyticsOrderRegion');

        $filter = new Filter\OrderRegion\Filter();
        $form = $this->createForm(Filter\OrderRegion\Form::class, $filter);
        $form->handleRequest($request);

        ini_set('max_execution_time', '900');

        $pagination = $fetcher->all($filter, $settings);
        $sumIncome = 0;
        if ($pagination) {
            foreach ($pagination->getItems() as $item) {
                $sumIncome += $item['income'];
            }
        }

        return $this->render('app/analytics/orderRegions/index.html.twig', [
            'pagination' => $pagination,
            'sumIncome' => $sumIncome,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/numbers/", name=".numbers")
     * @param Provider $provider
     * @param AnalyticsOrderRegionFetcher $fetcher
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function numbers(Provider $provider, AnalyticsOrderRegionFetcher $fetcher, Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsOrderRegion');

        $filter = new Filter\OrderRegion\Filter();
        $form = $this->createForm(Filter\OrderRegion\Form::class, $filter);
        $form->handleRequest($request);

        $all = $fetcher->numbers($filter, $provider);

//        $all = $fetcher->allByIncome($income->getId());

        return $this->render('app/analytics/orderRegions/_numbers.html.twig', [
            'all' => $all
        ]);

    }
}
