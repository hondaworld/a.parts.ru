<?php

namespace App\Controller\Sklad;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\EntityNotFoundException;
use App\Model\Expense\UseCase\Sklad\Pack;
use App\Model\Expense\UseCase\Sklad\Delete;
use App\Model\Expense\UseCase\Sklad\Send;
use App\Model\Expense\UseCase\Sklad\Income;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Expense\ExpenseIncomeFetcher;
use App\ReadModel\Expense\ExpenseShippingFetcher;
use App\ReadModel\Expense\Filter;
use App\ReadModel\Shop\ShopLocationFetcher;
use App\Security\Voter\ExpenseSklad\ExpenseShippingVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/sklads/income", name="sklads.income")
 */
class ZapSkladIncomeController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ExpenseIncomeFetcher $fetcher
     * @param ShopLocationFetcher $shopLocationFetcher
     * @param WeightFetcher $weightFetcher
     * @param ZapCardRepository $zapCardRepository
     * @param ManagerSettings $settings
     * @return Response
     * @throws Exception
     */
    public function index(ZapSklad $zapSklad, Request $request, ExpenseIncomeFetcher $fetcher, ShopLocationFetcher $shopLocationFetcher, WeightFetcher $weightFetcher, ZapCardRepository $zapCardRepository, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ExpenseSklad');

        $settings = $settings->get('skladsIncome');

        $filter = new Filter\Income\Filter();

        $form = $this->createForm(Filter\Income\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->allIncome(
            $zapSklad,
            $filter,
            $settings
        );

        $zapCards = [];
        $arZapCards = [];
        $arWeights = [];

        if ($pagination) {
            $items = $pagination->getItems();
            foreach ($items as $item) {
                $arZapCards[] = $item['zapCardID'];
            }

            if ($arZapCards) {
                $zapCards = $zapCardRepository->findByZapCards($arZapCards);
            }

            $locations = $shopLocationFetcher->findByZapCards($arZapCards);
            $expenses = $fetcher->findByZapCards($zapSklad, $arZapCards);

            foreach ($items as &$item) {

                if (!isset($arWeights[$item['zapCardID']])) {
                    $weight = $weightFetcher->allByNumberAndCreater($item['number'], $item['createrID']);
                    $arWeights[$item['zapCardID']] = $weight ? $weight[0] : null;
                }
                $item['weight'] = $arWeights[$item['zapCardID']];

                $quantitySklad = 0;
                $quantitySklad1 = 0;
                $documents = [];

                $item['order'] = '';
                $item['documents'] = [];
                foreach ($expenses[$item['zapCardID']] as $expense) {
                    $item['status'] = $expense['status'];

                    $documents[$expense['expense_skladDocumentID']] = [
                        'document_num' => $expense['document_num'],
                        'document_date' => $expense['document_date'],
                    ];

                    if ($expense['providerPriceID']) {
                        $item['location'] .= $expense['provider_price_name'] . ' - ' . $expense['quantity'] . "шт.\n";
                    } else {
                        $quantitySklad += $expense['quantity'];
                    }
                    if ($expense['orderID']) {
                        $item['order'] .= $expense['orderID'] . ' - ' . $expense['quantity'] . "шт.\n";
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
                if ($documents) {
                    $item['documents'] = $documents;
                }
            }

            $sort = $settings['sort'] ?? ExpenseIncomeFetcher::DEFAULT_SORT_FIELD_NAME;
            $direction = $settings['direction'] ?? ExpenseIncomeFetcher::DEFAULT_SORT_DIRECTION;
            if ($sort == 'location') {
                uasort($items, function ($a, $b) use ($direction) {
                    if ($direction == 'asc') {
                        return $a['location'] <=> $b['location'];
                    } else {
                        return $b['location'] <=> $a['location'];
                    }
                });
            }
            $pagination->setItems($items);
        }

        if ($request->query->get('print') ?? 0) {
            return $this->render('app/sklads/income/print.html.twig', [
                'pagination' => $pagination,
                'zapSklad' => $zapSklad,
                'locations' => $locations ?? [],
                'expenses' => $expenses ?? [],
                'zapCards' => $zapCards,
            ]);
        } else {
            return $this->render('app/sklads/income/index.html.twig', [
                'table_checkable' => true,
                'pagination' => $pagination,
                'filter' => $form->createView(),
                'zapSklad' => $zapSklad,
                'locations' => $locations ?? [],
                'expenses' => $expenses ?? [],
                'zapCards' => $zapCards,
            ]);
        }
    }

    /**
     * @Route("/{id}/income", name=".income")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param Income\Handler $handler
     * @return Response
     */
    public function income(ZapSklad $zapSklad, Request $request, Income\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_INCOME_INCOME, 'ExpenseSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $cols = $request->request->get('cols');
            foreach ($cols as $id) {
                $arr = explode('_', $id);
                $zapCardID = $arr[0];
                $zapSkladID = $arr[1];
                $handler->handle($zapCardID, $zapSkladID, $zapSklad->getId());
            }

//                $messages = $handler->handle($command, $zapSklad);
//
//                if ($messages) {
//                    foreach ($messages as $message) {
//                        $this->addFlash($message['type'], $message['message']);
//                    }
//                } else {
//                    $data['reload'] = true;
//                }
            $data['reload'] = true;

        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }
}
