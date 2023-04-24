<?php

namespace App\Controller\Finance;

use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Finance\Entity\CurrencyRate\CurrencyRate;
use App\Model\Finance\Entity\CurrencyRate\CurrencyRateRepository;
use App\Model\Finance\UseCase\CurrencyRate\Edit;
use App\Model\Finance\UseCase\CurrencyRate\Create;
use App\Model\Flusher;
use App\ReadModel\Finance\Filter;
use App\ReadModel\Finance\CurrencyRateFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/finance/currency/rates", name="currency.rates")
 */
class CurrencyRatesController extends AbstractController
{
    /**
     * @Route("/{currencyID}/", name="")
     * @param Currency $currency
     * @param Request $request
     * @param CurrencyRateFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Currency $currency, Request $request, CurrencyRateFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Currency');

        $settings = $settings->get('currencyRate');

        $filter = new Filter\CurrencyRate\Filter();
        $filter->inPage = isset($settings['inPage']) ? $settings['inPage'] : $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\CurrencyRate\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $currency,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );


        return $this->render('app/finance/currencyRate/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'currency' => $currency,
        ]);
    }

    /**
     * @Route("/{currencyID}/create", name=".create")
     * @ParamConverter("currency", options={"id" = "currencyID"})
     * @param Currency $currency
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Currency $currency, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Currency');

        $command = new Create\Command($currency);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('currency.rates', ['currencyID' => $currency->getId(), 'page' => $request->getSession()->get('page/currencyRate') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/currencyRate/create.html.twig', [
            'form' => $form->createView(),
            'currency' => $currency,
            'page' => $request->getSession()->get('page/currencyRate')
        ]);
    }

    /**
     * @Route("/{currencyID}/{id}/edit", name=".edit")
     * @ParamConverter("currency", options={"id" = "currencyID"})
     * @param Currency $currency
     * @param CurrencyRate $currencyRate
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Currency $currency, CurrencyRate $currencyRate, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Currency');

        $command = Edit\Command::fromCurrencyRate($currencyRate);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('currency.rates', ['currencyID' => $currency->getId(), 'page' => $request->getSession()->get('page/currencyRate') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/currencyRate/edit.html.twig', [
            'form' => $form->createView(),
            'currency' => $currency,
            'page' => $request->getSession()->get('page/currencyRate')
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param CurrencyRepository $rates
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, Request $request, CurrencyRateRepository $rates, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Currency');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $currency = $rates->get($id);
            $em->remove($currency);
            $flusher->flush();
            $data['message'] = 'Курс валюты удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
