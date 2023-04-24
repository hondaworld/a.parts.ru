<?php

namespace App\Controller\Sklad;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\EntityNotFoundException;
use App\Model\Expense\UseCase\Sklad\QuantityIncome;
use App\Model\Expense\UseCase\Sklad\DeleteIncome;
use App\Model\Expense\UseCase\Sklad\Income;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Expense\ExpenseIncomeFetcher;
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
 * @Route("/sklads/income", name="sklads.income")
 */
class ZapSkladIncomeScanController extends AbstractController
{
    /**
     * @Route("/{id}/scan", name=".scan")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ExpenseIncomeFetcher $fetcher
     * @param ShopLocationFetcher $shopLocationFetcher
     * @param QuantityIncome\Handler $handler
     * @param Income\Handler $incomeHandler
     * @return Response
     * @throws Exception
     */
    public function index(ZapSklad $zapSklad, Request $request, ExpenseIncomeFetcher $fetcher, ShopLocationFetcher $shopLocationFetcher, QuantityIncome\Handler $handler, Income\Handler $incomeHandler): Response
    {
        $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_INCOME_SCAN, 'ExpenseSklad');

        $command = new QuantityIncome\Command();
        $form = $this->createForm(QuantityIncome\Form::class, $command);
        $form->handleRequest($request);

        $searchNumber = $request->query->get('number') ? (new DetailNumber($request->query->get('number'))) : null;
        $scan = $request->query->get('scan') ?? 0;

        $expenseSklads = $fetcher->allIncomeAndNotScanned($zapSklad);
        $arZapCards = [];
        $expenseSklad = [];

        foreach ($expenseSklads as $item) {
            $arZapCards[] = $item['zapCardID'];
        }

        $locations = $shopLocationFetcher->findByZapCards($arZapCards);
        $expenses = $fetcher->findByZapCards($zapSklad, $arZapCards);

        foreach ($expenseSklads as &$item) {
            $item['order'] = '';
            $quantitySklad = 0;
            $quantitySklad1 = 0;
            foreach ($expenses[$item['zapCardID']] as $expense) {
                if ($expense['providerPriceID']) {
                    $item['location'] .= $expense['provider_price_name'] . ' - ' . $expense['quantity'] . "шт.\n";
                } else {
                    $quantitySklad += $expense['quantity'];
                }
                if ($expense['orderID']) {
                    $item['order'] .= $expense['orderID'] . ' (' . $expense['user_name'] . ')' . ' - ' . $expense['quantity'] . "шт.\n";
                } else {
                    $quantitySklad1 += $expense['quantity'];
                }
            }
            if ($quantitySklad > 0) {
                $item['location'] .= (isset($locations[$item['zapCardID']][$zapSklad->getId()]) ? $locations[$item['zapCardID']][$zapSklad->getId()]['location'] : 'Склад') . ' - ' . $quantitySklad . "шт.\n";
            }
            if ($quantitySklad1 > 0) {
                $item['order'] .= 'Склад' . ' - ' . $quantitySklad1 . "шт.\n";
            }

            if ($searchNumber != '' && $searchNumber->isEqual(new DetailNumber($item['number'])) && $item['quantity'] != $item['quantityIncome']) {
//                if ($item['quantity'] - $item['quantityIncome'] == 1) {
//                    $command->quantityIncome = 1;
//                } else {
                if ($scan != 2) return $this->redirectToRoute('sklads.income.scan', ['id' => $zapSklad->getId(), 'number' => $searchNumber->getValue(), 'scan' => 2]);
//                }
                $expenseSklad = $item;
            }
        }

        if (!$expenseSklad && $searchNumber) {
            $this->addFlash('error', 'Номер не найден');
        }

        if ($expenseSklad && ($form->isSubmitted() && $form->isValid())) {
            try {
                $handler->handle($command, $expenseSklad, $expenses[$expenseSklad['zapCardID']]);

                if (!$fetcher->hasIncomeByZapCard($expenseSklad['zapCardID'], $expenseSklad['zapSkladID'], $zapSklad->getId())) {
                    $incomeHandler->handle($expenseSklad['zapCardID'], $expenseSklad['zapSkladID'], $zapSklad->getId());
                }

                if ($fetcher->hasIncome($expenseSklad['zapSkladID'], $zapSklad->getId())) {
                    return $this->redirectToRoute('sklads.income.scan', ['id' => $zapSklad->getId(), 'scan' => 1]);
                } else {
                    $this->addFlash('success', 'Все детали оприходованы');
                    return $this->redirectToRoute('sklads.income.scan', ['id' => $zapSklad->getId()]);
                }
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }


        return $this->render('app/sklads/income/scan/index.html.twig', [
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
     * @param DeleteIncome\Handler $handler
     * @return Response
     */
    public function delete(ZapSklad $zapSklad, Request $request, DeleteIncome\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_INCOME_SCAN, 'ExpenseSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $zapCardID = $request->query->getInt('zapCardID');
            $zapSkladID = $request->query->getInt('zapSkladID');

            $handler->handle($zapCardID, $zapSkladID, $zapSklad);

            $data['redirectToUrl'] = $this->generateUrl('sklads.income.scan', ['id' => $zapSklad->getId()]);
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
