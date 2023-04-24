<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\EntityNotFoundException;
use App\Model\Card\UseCase\StockNumber\Edit;
use App\Model\Card\UseCase\StockNumber\Create;
use App\Model\Card\UseCase\StockNumber\Price;
use App\Model\Card\UseCase\StockNumber\Upload;
use App\Model\Flusher;
use App\ReadModel\Card\ZapCardStockNumberFetcher;
use App\Security\Voter\Card\ZapCardStockVoter;
use App\Service\ManagerSettings;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/stocks/numbers", name="card.stocks.numbers")
 */
class ZapCardStockNumbersController extends AbstractController
{
    /**
     * @Route("/{stockID}/", name="")
     * @param ZapCardStock $zapCardStock
     * @param ZapCardStockNumberFetcher $fetcher
     * @param ManagerSettings $settings
     * @param Request $request
     * @param Price\Handler $handler
     * @return Response
     */
    public function index(ZapCardStock $zapCardStock, ZapCardStockNumberFetcher $fetcher, ManagerSettings $settings, Request $request, Price\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ZapCardStockVoter::ZAP_CARD_STOCK_NUMBERS, $zapCardStock);

        $settings = $settings->get('zapCardStocks');

        $pagination = $fetcher->allByStock($zapCardStock, $settings);

        $stockNumbers = [];
        foreach ($pagination->getItems() as $item) {
            $stockNumbers[$item['numberID']] = $item['price_stock'];
        }

        $command = new Price\Command($stockNumbers);
        $form = $this->createForm(Price\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Цены сохранены');
                return $this->redirectToRoute('card.stocks.numbers', ['stockID' => $zapCardStock->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/stockNumbers/index.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
            'stock' => $zapCardStock,
        ]);
    }

    /**
     * @Route("/{stockID}/create", name=".create")
     * @param ZapCardStock $zapCardStock
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(ZapCardStock $zapCardStock, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ZapCardStockVoter::ZAP_CARD_STOCK_NUMBERS_CHANGE, $zapCardStock);

        $command = new Create\Command($zapCardStock);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.stocks.numbers', ['stockID' => $zapCardStock->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/stockNumbers/create.html.twig', [
            'form' => $form->createView(),
            'stock' => $zapCardStock
        ]);
    }

    /**
     * @Route("/{stockID}/upload", name=".upload")
     * @param ZapCardStock $zapCardStock
     * @param Request $request
     * @param Upload\Handler $handler
     * @return Response
     */
    public function upload(ZapCardStock $zapCardStock, Request $request, Upload\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ZapCardStockVoter::ZAP_CARD_STOCK_NUMBERS_CHANGE, $zapCardStock);

        $command = new Upload\Command($zapCardStock);

        $form = $this->createForm(Upload\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $handler->handle($command, $file);
                }
                return $this->redirectToRoute('card.stocks.numbers', ['stockID' => $zapCardStock->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/stockNumbers/upload.html.twig', [
            'form' => $form->createView(),
            'stock' => $zapCardStock
        ]);
    }

    /**
     * @Route("/{stockID}/{id}/edit", name=".edit")
     * @ParamConverter("zapCardStock", options={"id" = "stockID"})
     * @param ZapCardStock $zapCardStock
     * @param ZapCardStockNumber $zapCardStockNumber
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ZapCardStock $zapCardStock, ZapCardStockNumber $zapCardStockNumber, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ZapCardStockVoter::ZAP_CARD_STOCK_NUMBERS_CHANGE, $zapCardStock);

        $command = Edit\Command::fromEntity($zapCardStockNumber);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.stocks.numbers', ['stockID' => $zapCardStock->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/stockNumbers/edit.html.twig', [
            'form' => $form->createView(),
            'stock' => $zapCardStock,
            'stockNumber' => $zapCardStockNumber
        ]);
    }

    /**
     * @Route("/{stockID}/{id}/delete", name=".delete")
     * @ParamConverter("zapCardStock", options={"id" = "stockID"})
     * @param ZapCardStock $zapCardStock
     * @param ZapCardStockNumber $zapCardStockNumber
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapCardStock $zapCardStock, ZapCardStockNumber $zapCardStockNumber, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ZapCardStockVoter::ZAP_CARD_STOCK_NUMBERS_CHANGE, $zapCardStock);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($zapCardStockNumber);
            $flusher->flush();
            $data['message'] = 'Запчасть удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
