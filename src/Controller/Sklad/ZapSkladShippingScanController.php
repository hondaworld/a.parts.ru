<?php

namespace App\Controller\Sklad;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\EntityNotFoundException;
use App\Model\Expense\UseCase\Sklad\QuantityPicking;
use App\Model\Expense\UseCase\Sklad\DeletePicking;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Expense\ExpenseShippingFetcher;
use App\ReadModel\Expense\Filter;
use App\ReadModel\Shop\ShopLocationFetcher;
use App\Security\Voter\ExpenseSklad\ExpenseShippingVoter;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/sklads/shipping", name="sklads.shipping")
 */
class ZapSkladShippingScanController extends AbstractController
{
    /**
     * @Route("/{id}/scan", name=".scan")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ExpenseShippingFetcher $fetcher
     * @param ShopLocationFetcher $shopLocationFetcher
     * @param QuantityPicking\Handler $handler
     * @return Response
     * @throws Exception
     */
    public function index(ZapSklad $zapSklad, Request $request, ExpenseShippingFetcher $fetcher, ShopLocationFetcher $shopLocationFetcher, QuantityPicking\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_SHIPPING_SCAN, 'ExpenseSklad');

        $command = new QuantityPicking\Command();
        $form = $this->createForm(QuantityPicking\Form::class, $command);
        $form->handleRequest($request);

        $searchNumber = $request->query->get('number') ? (new DetailNumber($request->query->get('number'))) : null;
        $scan = $request->query->get('scan') ?? 0;

        $expenseSklads = $fetcher->allShippingAndPacked($zapSklad);
        $arZapCards = [];
        $expenseSklad = [];

        foreach ($expenseSklads as $item) {
            $arZapCards[] = $item['zapCardID'];
        }

        $locations = $shopLocationFetcher->findByZapCards($arZapCards);
        $expenses = $fetcher->findByZapCards($zapSklad, $arZapCards);

        foreach ($expenseSklads as &$item) {
            $quantitySklad = 0;
            foreach ($expenses[$item['zapCardID']] as $expense) {
                if ($expense['providerPriceID']) {
                    $item['location'] .= $expense['provider_price_name'] . ' - ' . $expense['quantity'] . "шт.\n";
                } else {
                    $quantitySklad += $expense['quantity'];
                }
            }
            if ($quantitySklad > 0) {
                $item['location'] .= (isset($locations[$item['zapCardID']][$zapSklad->getId()]) ? $locations[$item['zapCardID']][$zapSklad->getId()]['location'] : 'Склад') . ' - ' . $quantitySklad . "шт.\n";
            }

            if ($searchNumber != '' && $searchNumber->isEqual(new DetailNumber($item['number'])) && $item['quantity'] != $item['quantityPicking']) {
                if ($item['quantity'] - $item['quantityPicking'] == 1) {
                    $command->quantityPicking = 1;
                } else {
                    if ($scan != 2) return $this->redirectToRoute('sklads.shipping.scan', ['id' => $zapSklad->getId(), 'number' => $searchNumber->getValue(), 'scan' => 2]);
                }
                $expenseSklad = $item;
            }
        }

        if (!$expenseSklad && $searchNumber) {
            $this->addFlash('error', 'Номер не найден');
        }

        if ($expenseSklad && ($form->isSubmitted() && $form->isValid() || $command->quantityPicking)) {
            try {
                $handler->handle($command, $expenseSklad, $expenses[$expenseSklad['zapCardID']]);
                if ($fetcher->hasPicking($zapSklad->getId(), $expenseSklad['zapSkladID_to'])) {
                    return $this->redirectToRoute('sklads.shipping.scan', ['id' => $zapSklad->getId(), 'scan' => 1]);
                } else {
                    $this->addFlash('success', 'Все детали собраны');
                    return $this->redirectToRoute('sklads.shipping.scan', ['id' => $zapSklad->getId()]);
                }
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }


        return $this->render('app/sklads/shipping/scan/index.html.twig', [
            'zapSklad' => $zapSklad,
            'expenseSklads' => $expenseSklads,
            'expenseSklad' => $expenseSklad,
            'locations' => $locations ?? [],
            'expenses' => $expenses ?? [],
            'form' => $form->createView(),
            'searchNumber' => $searchNumber,
        ]);
    }

    /**
     * @Route("/{id}/scan/delete", name=".scan.delete")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param DeletePicking\Handler $handler
     * @return Response
     */
    public function delete(ZapSklad $zapSklad, Request $request, DeletePicking\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_SHIPPING_SCAN, 'ExpenseSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $zapCardID = $request->query->getInt('zapCardID');
            $zapSkladID_to = $request->query->getInt('zapSkladID_to');

            $handler->handle($zapCardID, $zapSklad, $zapSkladID_to);

            $data['redirectToUrl'] = $this->generateUrl('sklads.shipping.scan', ['id' => $zapSklad->getId()]);
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
