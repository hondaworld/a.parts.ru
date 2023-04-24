<?php

namespace App\Controller\Analytics;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\ReadModel\Analytics\Filter;
use App\ReadModel\Analytics\AnalyticsPriceFixFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/analytics/priceFix", name="analytics.priceFix")
 */
class AnalyticsPriceFixController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param AnalyticsPriceFixFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, AnalyticsPriceFixFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsPriceFix');

        $settings = $settings->get('analyticsPriceFix');

        $filter = new Filter\PriceFix\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;
        $form = $this->createForm(Filter\PriceFix\Form::class, $filter);
        $form->handleRequest($request);


        ini_set('max_execution_time', '900');

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings);

        return $this->render('app/analytics/priceFix/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/fix/", name=".fix")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function fix(ZapCard $zapCard, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsPriceFix');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
//            $name = $request->query->get('name');
            $is_price_group_fix = $request->query->getBoolean('checked');

            $zapCard->updatePriceGroupFix($is_price_group_fix);
            $flusher->flush();

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);

    }
}
