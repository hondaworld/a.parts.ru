<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumberRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Card\UseCase\Card\Profit;
use App\Model\Card\UseCase\Card\ProfitAll;
use App\Model\Card\UseCase\Card\PriceService;
use App\Model\Card\UseCase\Card\Price;
use App\Model\Card\UseCase\Card\ProfitZapCard;
use App\Model\Card\UseCase\Card\ProfitPriceGroup;
use App\Model\Card\UseCase\Card\Stock;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Card\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/card/parts", name="card.parts")
 */
class ZapCardPricesController extends AbstractController
{
    /**
     * @Route("/{id}/priceService", name=".priceService")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param PriceService\Handler $handler
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function priceService(ZapCard $zapCard, Request $request, PriceService\Handler $handler, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $opts = $optRepository->findAllOrdered();
        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        $command = PriceService\Command::fromEntity($zapCard);

        $form = $this->createForm(PriceService\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.prices', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'form' => $form->createView(),
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => 'priceService',
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/price", name=".price")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Price\Handler $handler
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function price(ZapCard $zapCard, Request $request, Price\Handler $handler, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $opts = $optRepository->findAllOrdered();
        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        $command = Price\Command::fromEntity($zapCard);

        $form = $this->createForm(Price\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.prices', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'form' => $form->createView(),
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => 'price',
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/profit", name=".profit")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Profit\Handler $handler
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function profit(ZapCard $zapCard, Request $request, Profit\Handler $handler, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $opts = $optRepository->findAllOrdered();
        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        $command = Profit\Command::fromEntity($zapCard);

        $form = $this->createForm(Profit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.prices', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'form' => $form->createView(),
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => 'profit',
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/profitAll", name=".profitAll")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ProfitAll\Handler $handler
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function profitAll(ZapCard $zapCard, Request $request, ProfitAll\Handler $handler, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $opts = $optRepository->findAllOrdered();
        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        $command = ProfitAll\Command::fromEntity($zapCard, $opts, $profitsFromZapCard);

        $form = $this->createForm(ProfitAll\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.prices', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'form' => $form->createView(),
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => 'profitAll',
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/profitZapCard", name=".profitZapCard")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ProfitZapCard\Handler $handler
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function profitZapCard(ZapCard $zapCard, Request $request, ProfitZapCard\Handler $handler, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $opts = $optRepository->findAllOrdered();
        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        $command = ProfitZapCard\Command::fromEntity($zapCard, $opts, $profitsFromZapCard);

        $form = $this->createForm(ProfitZapCard\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.prices', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'form' => $form->createView(),
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => 'profitZapCard',
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/profitPriceGroup", name=".profitPriceGroup")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ProfitPriceGroup\Handler $handler
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function profitPriceGroup(ZapCard $zapCard, Request $request, ProfitPriceGroup\Handler $handler, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $opts = $optRepository->findAllOrdered();
        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        $command = ProfitPriceGroup\Command::fromEntity($zapCard);

        $form = $this->createForm(ProfitPriceGroup\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.prices', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'form' => $form->createView(),
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => 'profitPriceGroup',
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{id}/stock", name=".stock")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Stock\Handler $handler
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function stock(ZapCard $zapCard, Request $request, Stock\Handler $handler, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $opts = $optRepository->findAllOrdered();
        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        $command = Stock\Command::fromEntity($zapCard, $stock);

        $form = $this->createForm(Stock\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.prices', ['id' => $zapCard->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'form' => $form->createView(),
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => 'stock',
            'zapCard' => $zapCard
        ]);
    }
}
