<?php

namespace App\Controller\Sklad;

use App\ReadModel\Expense\Filter;
use App\ReadModel\Expense\ExpenseSkladFetcher;
use App\ReadModel\Shop\ShopLocationFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sklads/expenses", name="sklads.expenses")
 */
class ExpenseSkladController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ExpenseSkladFetcher $fetcher
     * @param ShopLocationFetcher $shopLocationFetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, ExpenseSkladFetcher $fetcher, ShopLocationFetcher $shopLocationFetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ExpenseSklad');

        $settings = $settings->get('expenseSklads');

        $filter = new Filter\ExpenseSklad\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ExpenseSklad\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->allGroupByDocument(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $arZapCards = [];
        $arExpense_skladDocuments = [];

        if ($pagination) {
            $items = $pagination->getItems();
            foreach ($items as $item) {
                $arZapCards[] = $item['zapCardID'];
                $arExpense_skladDocuments[] = $item['expense_skladDocumentID'];
            }
            $pagination->setItems($items);

            $locations = $shopLocationFetcher->findByZapCards($arZapCards);
            $expenses = $fetcher->findByDocuments($arExpense_skladDocuments);
        }

        return $this->render('app/sklads/expenses/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'locations' => $locations ?? [],
            'expenses' => $expenses ?? [],
        ]);
    }
}
