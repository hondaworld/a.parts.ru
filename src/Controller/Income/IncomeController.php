<?php


namespace App\Controller\Income;


use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Card\UseCase\Card\Name;
use App\Model\Card\UseCase\Card\Country;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Income\UseCase\Income\Gtd;
use App\Model\Income\UseCase\Income\Number;
use App\Model\Income\UseCase\Income\ProviderPrice;
use App\Model\Income\UseCase\Income\Price;
use App\Model\Income\UseCase\Income\PriceSelected;
use App\Model\Income\UseCase\Income\Weight;
use App\Model\Income\UseCase\Income\DateOfZakaz;
use App\Model\Income\UseCase\Income\DateOfIn;
use App\Model\Income\UseCase\Income\DateOfInPlan;
use App\Model\Income\UseCase\Income\Quantity;
use App\Model\Income\UseCase\Income\QuantityChange;
use App\Model\Income\UseCase\Income\QuantityAll;
use App\Model\Income\Entity\Income\Income;
use App\ReadModel\Document\DocumentTypeFetcher;
use App\ReadModel\Expense\ExpenseFetcher;
use App\ReadModel\Expense\ExpenseSkladFetcher;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Income\Filter;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Income\IncomeGoodFetcher;
use App\ReadModel\Income\IncomeSkladFetcher;
use App\ReadModel\Income\IncomeStatusHistoryFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\Income\IncomeVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Price\PartPriceService;
use App\Service\WindowCoordsService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/income", name="income")
 */
class IncomeController extends AbstractController
{
    /**
     * @Route("/{id}/country", name=".country")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Country\Handler $handler
     * @param CountryRepository $countryRepository
     * @return Response
     */
    public function country(ZapCard $zapCard, Request $request, Country\Handler $handler, CountryRepository $countryRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = Country\Command::fromEntity($zapCard);
        $form = $this->createForm(Country\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            //$data['message'] = 'Цена изменена';
            try {
                $handler->handle($command);
                //$data['reload'] = true;
                $data['dataIdentification'] = [
                    [
                        'value' => $zapCard->getId(),
                        'name' => 'zapCardID'
                    ]
                ];
                $data['dataValue'] = [
                    [
                        'value' => $command->countryID,
                        'name' => 'countryID'
                    ]
                ];
                $data['ident'] = 'country';
                $data['value'] = $command->countryID ? $countryRepository->get($command->countryID)->getName() : '';
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/dateofzakaz", name=".dateofzakaz")
     * @param Income $income
     * @param Request $request
     * @param DateOfZakaz\Handler $handler
     * @return Response
     */
    public function dateofzakaz(Income $income, Request $request, DateOfZakaz\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = DateOfZakaz\Command::fromEntity($income);
        $form = $this->createForm(DateOfZakaz\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);
                $data['dataIdentification'] = [
                    ['value' => $income->getId(), 'name' => 'incomeID']
                ];
                $data['dataValue'] = [
                    ['value' => $command->dateofzakaz->format('d.m.Y'), 'name' => 'dateofzakaz']
                ];
                $data['ident'] = 'dateofzakaz';
                $data['value'] = $command->dateofzakaz->format('d.m.Y');
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/dateofin", name=".dateofin")
     * @param Income $income
     * @param Request $request
     * @param DateOfIn\Handler $handler
     * @return Response
     */
    public function dateofin(Income $income, Request $request, DateOfIn\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = DateOfIn\Command::fromEntity($income);
        $form = $this->createForm(DateOfIn\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);
                $data['dataIdentification'] = [
                    ['value' => $income->getId(), 'name' => 'incomeID']
                ];
                $data['dataValue'] = [
                    ['value' => $command->dateofin->format('d.m.Y'), 'name' => 'dateofin']
                ];
                $data['ident'] = 'dateofin';
                $data['value'] = $command->dateofin->format('d.m.Y');
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/dateofinplan", name=".dateofinplan")
     * @param Income $income
     * @param Request $request
     * @param DateOfInPlan\Handler $handler
     * @return Response
     */
    public function dateofinplan(Income $income, Request $request, DateOfInPlan\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_DATE_IN_PLAN, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = DateOfInPlan\Command::fromEntity($income);
        $form = $this->createForm(DateOfInPlan\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);
                $data['dataIdentification'] = [
                    ['value' => $income->getId(), 'name' => 'incomeID']
                ];
                $data['dataValue'] = [
                    ['value' => $command->dateofinplan->format('d.m.Y'), 'name' => 'dateofinplan']
                ];
                $data['ident'] = 'dateofinplan';
                $data['value'] = $command->dateofinplan->format('d.m.Y');
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/name", name=".name")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Name\Handler $handler
     * @return Response
     */
    public function name(ZapCard $zapCard, Request $request, Name\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = Name\Command::fromEntity($zapCard);
        $form = $this->createForm(Name\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
//            $data['message'] = 'Цена изменена';
            try {
                $handler->handle($command);
//                $data['reload'] = true;

                $data['dataIdentification'] = [
                    [
                        'value' => $zapCard->getId(),
                        'name' => 'zapCardID'
                    ]
                ];
                $data['dataValue'] = [
                    ['value' => $command->name ?: '', 'name' => 'name'],
                    ['value' => $command->name_big ?: '', 'name' => 'name_big'],
                    ['value' => $command->nameEng ?: '', 'name' => 'nameEng'],
                    ['value' => $command->description ?: '', 'name' => 'description'],
                    ['value' => $command->zapGroupID ?: '', 'name' => 'zapGroupID'],
                ];
                $data['ident'] = 'name';
                $data['value'] = $zapCard->getDetailName();
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/gtd", name=".gtd")
     * @param Income $income
     * @param Request $request
     * @param Gtd\Handler $handler
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function gtd(Income $income, Request $request, Gtd\Handler $handler, ValidatorInterface $validator): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = Gtd\Command::fromEntity($income);
        $command->gtd = $request->request->get('value');

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                $handler->handle($command);
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($errors as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/weight", name=".weight")
     * @param Income $income
     * @param Request $request
     * @param Weight\Handler $handler
     * @return Response
     */
    public function weight(Income $income, Request $request, Weight\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = Weight\Command::fromEntity($income);
        $form = $this->createForm(Weight\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
//            $data['message'] = 'Цена изменена';
            try {
                $handler->handle($command, $income);
////                $data['reload'] = true;
//
//                $data['inputIdentification'] = [
//                    ['value' => $income->getZapCard()->getNumber()->getValue(), 'name' => 'number'],
//                    ['value' => $income->getZapCard()->getCreater()->getId(), 'name' => 'createrID']
//                ];

                $data['idIdentification'] = [];
                foreach ($command->incomes as $incomeID => $incomePrice) {
                    $data['idIdentification'][] = ['value' => number_format($incomePrice['priceDost'], 2, ',', ' '), 'name' => 'priceDost_' . $incomeID];
                    $data['idIdentification'][] = ['value' => number_format($incomePrice['price'], 2, ',', ' '), 'name' => 'price_' . $incomeID];
                }

                $data['dataIdentification'] = [
                    ['value' => $income->getZapCard()->getNumber()->getValue(), 'name' => 'number'],
                    ['value' => $income->getZapCard()->getCreater()->getId(), 'name' => 'createrID'],
                ];
                $data['dataValue'] = [
                    ['value' => $command->weight ? number_format($command->weight, 4, '.', '') : '', 'name' => 'weight'],
                    ['value' => $command->weightIsReal ? 1 : 0, 'name' => 'weightIsReal'],
                ];

                if ($command->weightIsReal) {
                    $data['addClasses'] = ['text-success'];
                } else {
                    $data['removeClasses'] = ['text-success'];
                }

                $data['ident'] = 'weight';
                $data['value'] = number_format($command->weight, 4, '.', '');
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/price", name=".price")
     * @param Income $income
     * @param Request $request
     * @param Price\Handler $handler
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function price(Income $income, Request $request, Price\Handler $handler, ValidatorInterface $validator): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = Price\Command::fromEntity($income);
        $priceZak = str_replace(',', '.', $request->request->get('value'));
        $priceZak = preg_replace("/[^0-9\.]/", "", $priceZak);
        $command->priceZak = floatval($priceZak);

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                $handler->handle($command);

                $data['idIdentification'] = [
                    ['value' => number_format($command->priceDost, 2, ',', ' '), 'name' => 'priceDost_' . $income->getId()],
                    ['value' => number_format($command->price, 2, ',', ' '), 'name' => 'price_' . $income->getId()]
                ];

            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($errors as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/priceSelected", name=".priceSelected")
     * @param Request $request
     * @param PriceSelected\Handler $handler
     * @return Response
     */
    public function priceSelected(Request $request, PriceSelected\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new PriceSelected\Command();
        $command->cols = $request->request->get('cols');


        try {
            $messages = $handler->handle($command);

            foreach ($messages as $message) {
                $this->addFlash($message['type'], $message['message']);
            }

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/statusHistory", name=".statusHistory")
     * @param Income $income
     * @param IncomeStatusHistoryFetcher $fetcher
     * @return Response
     */
    public function statusHistory(Income $income, IncomeStatusHistoryFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');

        $all = $fetcher->allByIncome($income->getId());

        return $this->render('app/income/statusHistory/index.html.twig', [
            'all' => $all
        ]);
    }

    /**
     * @Route("/{id}/providerPrices", name=".providerPrices")
     * @param Income $income
     * @param PartPriceService $partPriceService
     * @return Response
     * @throws Exception
     */
    public function providerPrices(Income $income, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');

        $command = new ProviderPrice\Command();
        $form = $this->createForm(ProviderPrice\Form::class, $command);

        $prices = $partPriceService->fullPriceByIncome($income);

        return $this->render('app/income/providerPrice/form.html.twig', [
            'income' => $income,
            'prices' => $prices,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/providerPrice/update", name=".providerPrice.update")
     * @param Income $income
     * @param Request $request
     * @param WindowCoordsService $windowCoordsService
     * @param ProviderPrice\Handler $handler
     * @return Response
     */
    public function providerPriceUpdate(Income $income, Request $request, WindowCoordsService $windowCoordsService, ProviderPrice\Handler $handler): Response
    {
//        try {
//            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');
//        } catch (AccessDeniedException $e) {
//            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
//        }
//
//        $data = ['code' => 200, 'message' => ''];
//
//        $command = new ProviderPrice\Command();
//        $command->providerPriceID = $request->query->get('providerPriceID') ?? ($request->query->get('form')['providerPriceID'] ?? null);
//
//        if ($command->providerPriceID) {
//            try {
//                $handler->handle($command, $income);
//                $data['reload'] = true;
//
//            } catch (DomainException $e) {
////                $this->addFlash('error', $e->getMessage());
//                $data['code'] = 404;
//                $data['message'] = $e->getMessage();
//            }
//        } else {
////            $this->addFlash('error', 'Параметр не задан');
//            $data['code'] = 404;
//            $data['message'] = 'Параметр не задан';
//        }
////        return $this->redirectToRoute('income', ['page' => $request->getSession()->get('page/income') ?: 1]);
//        return $this->json($data);

        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');

        $command = new ProviderPrice\Command();
        $command->providerPriceID = $request->query->get('providerPriceID') ?? ($request->query->get('form')['providerPriceID'] ?? null);
        $windowTop = $request->query->get('windowTop') ?? ($request->query->get('form')['windowTop'] ?? 0);
        if ($windowTop > 0) {
            $windowCoordsService->putTop($windowTop);
        }

        if ($command->providerPriceID) {
            try {
                $handler->handle($command, $income);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Параметр не задан');
        }
        return $this->redirectToRoute('income', ['page' => $request->getSession()->get('page/income') ?: 1]);
    }

    /**
     * @Route("/{id}/number", name=".number")
     * @param Income $income
     * @param Request $request
     * @param Number\Handler $handler
     * @return Response
     */
    public function number(Income $income, Request $request, Number\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = Number\Command::fromEntity($income);
        $form = $this->createForm(Number\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);
                $data['reload'] = true;
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sklads", name=".sklads")
     * @param Income $income
     * @param ZapSkladFetcher $fetcher
     * @param IncomeSkladFetcher $incomeSkladFetcher
     * @return Response
     * @throws Exception
     */
    public function sklads(Income $income, ZapSkladFetcher $fetcher, IncomeSkladFetcher $incomeSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');

        $sklads = $fetcher->assoc();

        $orderedOnSklads = $incomeSkladFetcher->findOrderedZapCardInAllSklads($income->getZapCard()->getId());

        $quantityOnSklads = $incomeSkladFetcher->findQuantityZapCardInAllSklads($income->getZapCard()->getId());

        return $this->render('app/income/sklads/index.html.twig', [
            'sklads' => $sklads,
            'orderedOnSklads' => $orderedOnSklads,
            'quantityOnSklads' => $quantityOnSklads
        ]);
    }

    /**
     * @Route("/{id}/incomeHistory", name=".incomeHistory")
     * @param Income $income
     * @param IncomeFetcher $incomeFetcher
     * @param IncomeGoodFetcher $incomeGoodFetcher
     * @param ExpenseFetcher $expenseFetcher
     * @param ExpenseSkladFetcher $expenseSkladFetcher
     * @param DocumentTypeFetcher $documentTypeFetcher
     * @param FirmFetcher $firmFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeSkladFetcher $incomeSkladFetcher
     * @return Response
     * @throws Exception
     */
    public function incomeHistory(Income $income, IncomeFetcher $incomeFetcher, IncomeGoodFetcher $incomeGoodFetcher, ExpenseFetcher $expenseFetcher, ExpenseSkladFetcher $expenseSkladFetcher, DocumentTypeFetcher $documentTypeFetcher, FirmFetcher $firmFetcher, ZapSkladFetcher $zapSkladFetcher, IncomeSkladFetcher $incomeSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');

        $documentTypes = $documentTypeFetcher->unique();
        $firms = $firmFetcher->assoc();
        $sklads = $zapSkladFetcher->allSklads();
        $incomeSklads = $incomeSkladFetcher->findByIncome($income);

        $pn = $incomeFetcher->findPNByIncomeWithDocument($income);
        $vz = $incomeFetcher->findVZByIncomeWithDocument($income);
        $incomeGoods = $incomeGoodFetcher->findByIncomeWithDocument($income);
        $expenses = $expenseFetcher->findByIncomeWithDocument($income);
        $expenseSklads = $expenseSkladFetcher->findByIncomeWithDocument($income);

        $all = array_merge_recursive($pn, $vz, $incomeGoods, $expenses, $expenseSklads);

        $incomeQuantity = [];
        if ($incomeSklads) {
            foreach ($incomeSklads as $zapSkladID => $incomeSklad) {
                $incomeQuantity[$zapSkladID] = [
                    'quantity' => $incomeSklad['quantity'],
                    'quantityIn' => $incomeSklad['quantityIn'],
                    'quantityInDoc' => $incomeSklad['quantity'],
                    'quantityPerem' => 0,
                ];
            }
        }

        uasort($all, function ($a, $b) {
            return $a['dateofadded'] <=> $b['dateofadded'];
        });

        foreach ($all as $item) {
            if (isset($incomeQuantity[$item['zapSkladID']])) {
                if ($item['zapSkladID']) {
                    if ($item['doc_typeID'] == DocumentType::VZ) {
                        $incomeQuantity[$item['zapSkladID']]['quantityInDoc'] += $item['quantity'];
                    }
                    if (in_array($item['doc_typeID'], [DocumentType::RN, DocumentType::TCH])) {
                        $incomeQuantity[$item['zapSkladID']]['quantityInDoc'] -= $item['quantity'];
                    }
                    if ($item['doc_typeID'] == DocumentType::NP) {
                        $incomeQuantity[$item['zapSkladID']]['quantityPerem'] += $item['quantity'];
                    }
                } else {
                    if (in_array($item['doc_typeID'], [DocumentType::RN, DocumentType::TCH])) {
                        foreach ($incomeQuantity as &$itemQuantity) {
                            if ($itemQuantity['quantity'] > 0) {
                                $itemQuantity['quantityInDoc'] -= $item['quantity'];
                            }
                        }
                    }
                }
            }
        }

        return $this->render('app/income/incomeHistory/index.html.twig', [
            'income' => $income,
            'incomeSklads' => $incomeSklads,
            'documentTypes' => $documentTypes,
            'firms' => $firms,
            'sklads' => $sklads,
            'incomeQuantity' => $incomeQuantity,
            'all' => $all
        ]);
    }

    /**
     * @Route("/{id}/quantityAll", name=".quantityAll")
     * @param Income $income
     * @param Request $request
     * @param IncomeSkladFetcher $fetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param QuantityAll\Handler $handler
     * @return Response
     */
    public function quantityAll(Income $income, Request $request, IncomeSkladFetcher $fetcher, ZapSkladFetcher $zapSkladFetcher, QuantityAll\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $sklads = $zapSkladFetcher->assocAll();
        $incomeSklads = $fetcher->findByIncome($income);

        $command = QuantityAll\Command::fromEntity($income, $sklads, $incomeSklads);
        $form = $this->createForm(QuantityAll\Form::class, $command);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $income);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
            return $this->redirectToRoute('income', ['page' => $request->getSession()->get('page/income') ?: 1]);
        }

        return $this->render('app/income/quantityAll/form.html.twig', [
            'income' => $income,
            'skladIDs' => array_keys($incomeSklads),
            'sklads' => $sklads,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/quantity/update", name=".quantity.update")
     * @param Income $income
     * @param Request $request
     * @param Quantity\Handler $handler
     * @return Response
     */
    public function quantityUpdate(Income $income, Request $request, Quantity\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_QUANTITY, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = Quantity\Command::fromEntity($income);
        $form = $this->createForm(Quantity\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);
                $data['reload'] = true;
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/quantityChange/update", name=".quantityChange.update")
     * @param Income $income
     * @param Request $request
     * @param QuantityChange\Handler $handler
     * @return Response
     */
    public function quantityChangeUpdate(Income $income, Request $request, QuantityChange\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_QUANTITY, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = QuantityChange\Command::fromEntity($income);
        $form = $this->createForm(QuantityChange\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);
                $data['reload'] = true;
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }
}