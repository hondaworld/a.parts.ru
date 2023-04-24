<?php

namespace App\Controller\Order;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Security\Voter\Income\IncomeVoter;
use App\Security\Voter\Order\OrderVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Card\UseCase\Card\Name;
use App\Model\Order\UseCase\Good\Perem;
use App\Model\Order\UseCase\Good\Discount;
use App\Model\Order\UseCase\Good\ProviderPrice;
use App\Model\Order\UseCase\Good\Quantity;
use App\Model\Order\UseCase\Good\QuantityChange;
use App\Model\Order\UseCase\Good\Refuse;
use App\Model\Order\UseCase\Document\CreateReturn;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/goods", name="order.goods")
 */
class OrderGoodController extends AbstractController
{
    /**
     * @Route("/{id}/name", name=".name")
     * @param OrderGood $orderGood
     * @param Request $request
     * @param Name\Handler $handler
     * @param ZapCardRepository $zapCardRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function name(OrderGood $orderGood, Request $request, Name\Handler $handler, ZapCardRepository $zapCardRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $zapCard = $zapCardRepository->getOrCreate($orderGood->getNumber(), $orderGood->getCreater());
        $flusher->flush();

        $command = Name\Command::fromEntity($zapCard);
        $form = $this->createForm(Name\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);

                $data['dataIdentification'] = [
                    [
                        'value' => $orderGood->getNumber()->getValue(),
                        'name' => 'number'
                    ],
                    [
                        'value' => $orderGood->getCreater()->getId(),
                        'name' => 'createrID'
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
     * @Route("/perem", name=".perem")
     * @return Response
     */
    public function perem(): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_LOCATION, 'Order');

        $command = new Perem\Command();
        $form = $this->createForm(Perem\Form::class, $command);

        return $this->render('app/orders/goods/perem/form.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/perem/update", name=".perem.update")
     * @param Request $request
     * @param Perem\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function peremUpdate(Request $request, Perem\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_LOCATION, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Perem\Command();
        $form = $this->createForm(Perem\Form::class, $command);
        $form->handleRequest($request);
        $command->cols = $request->request->get('cols');

        $manager = $managerRepository->get($this->getUser()->getId());

        if ($form->isValid()) {
            try {
                $messages = $handler->handle($command, $manager);

                foreach ($messages as $message) {
                    $this->addFlash($message['type'], $message['message']);
                }

                $data['reload'] = true;
//                $data['messages'] = $messages;
//                $data['dataIdentification'] = [
//                    [
//                        'value' => $orderGood->getId(),
//                        'name' => 'goodID'
//                    ]
//                ];
//                $data['ident'] = 'location';
//                $data['value'] = $zapSklad->getNameShort();
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
     * @Route("/discount", name=".discount")
     * @return Response
     */
    public function discount(): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_PRICE, 'Order');

        $command = new Discount\Command();
        $form = $this->createForm(Discount\Form::class, $command);

        return $this->render('app/orders/goods/discount/form.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/discount/update", name=".discount.update")
     * @param Request $request
     * @param Discount\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function discountUpdate(Request $request, Discount\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_PRICE, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new Discount\Command();
        $form = $this->createForm(Discount\Form::class, $command);
        $form->handleRequest($request);
        $command->cols = $request->request->get('cols');

        if ($form->isValid()) {
            try {
                $handler->handle($command, $manager);
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
     * @Route("/{id}/providerPrices", name=".providerPrices")
     * @param OrderGood $orderGood
     * @param PartPriceService $partPriceService
     * @return Response
     * @throws Exception
     */
    public function providerPrices(OrderGood $orderGood, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_LOCATION, 'Order');

        $command = new ProviderPrice\Command();
        $form = $this->createForm(ProviderPrice\Form::class, $command);

        $prices = $partPriceService->fullPriceByOrderGood($orderGood);
//        dump($prices);

        return $this->render('app/orders/goods/providerPrice/form.html.twig', [
            'orderGood' => $orderGood,
            'prices' => $prices,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/providerPrice/update", name=".providerPrice.update")
     * @param OrderGood $orderGood
     * @param Request $request
     * @param ProviderPrice\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function providerPriceUpdate(OrderGood $orderGood, Request $request, ProviderPrice\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_LOCATION, 'Order');

        $command = new ProviderPrice\Command();
        $command->providerPriceID = $request->query->get('providerPriceID') ?? ($request->query->get('form')['providerPriceID'] ?? null);
        $command->zapSkladID = $request->query->get('zapSkladID') ?? null;
        $command->isPrice = $request->query->get('isPrice') ?? null;

        $manager = $managerRepository->get($this->getUser()->getId());

        if ($command->providerPriceID || $command->zapSkladID) {
            try {
                $handler->handle($command, $orderGood, $manager);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Параметр не задан');
        }
        return $this->redirectToRoute('order.goods', ['id' => $orderGood->getOrder()->getUser()->getId()]);
    }

    /**
     * @Route("/documentReturn", name=".documentReturn")
     * @param Request $request
     * @param OrderGoodRepository $orderGoodRepository
     * @return Response
     */
    public function incomeDocumentReturnForm(Request $request, OrderGoodRepository $orderGoodRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_RETURN_DOCUMENT, 'Order');

        $returning = $request->query->get('returning');

        if ($returning != '') {
            $arReturning = explode(',', $returning);
        } else {
            $arReturning = [];
        }
        $orderGoods = [];
        if ($returning) {
            $orderGoods = $orderGoodRepository->findByIDs($arReturning);
        }


        $goods = [];
        foreach ($orderGoods as $orderGood) {
            $goods[$orderGood->getId()] = 0;
        }

        $command = new CreateReturn\Command($goods);
        $form = $this->createForm(CreateReturn\Form::class, $command);

        return $this->render('app/orders/order/documentReturn/form.html.twig', [
            'form' => $form->createView(),
            'orderGoods' => $orderGoods,
            'returning' => $returning
        ]);
    }

    /**
     * @Route("/documentReturn/create", name=".documentReturn.create")
     * @param Request $request
     * @param CreateReturn\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param OrderGoodRepository $orderGoodRepository
     * @return Response
     */
    public function incomeDocumentReturnCreate(Request $request, CreateReturn\Handler $handler, ManagerRepository $managerRepository, OrderGoodRepository $orderGoodRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_RETURN_DOCUMENT, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $returning = $request->query->get('returning');
        if ($returning != '') {
            $arReturning = explode(',', $returning);
        } else {
            $arReturning = [];
        }
        $orderGoods = [];
        if ($returning) {
            $orderGoods = $orderGoodRepository->findByIDs($arReturning);
        }

        $goods = [];
        foreach ($orderGoods as $orderGood) {
            $goods[$orderGood->getId()] = 0;
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new CreateReturn\Command($goods);
        $form = $this->createForm(CreateReturn\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $messages = $handler->handle($command, $manager);

//                dump($messages);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }
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
     * @Route("/{id}/quantity/update", name=".quantity.update")
     * @param OrderGood $orderGood
     * @param Request $request
     * @param Quantity\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function quantityUpdate(OrderGood $orderGood, Request $request, Quantity\Handler $handler, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_QUANTITY, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = Quantity\Command::fromEntity($orderGood);
        $form = $this->createForm(Quantity\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $manager);
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
     * @param OrderGood $orderGood
     * @param Request $request
     * @param QuantityChange\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function quantityChangeUpdate(OrderGood $orderGood, Request $request, QuantityChange\Handler $handler, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_QUANTITY, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = QuantityChange\Command::fromEntity($orderGood);
        $form = $this->createForm(QuantityChange\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $manager);
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
     * @Route("/refuse", name=".refuse")
     * @return Response
     */
    public function refuse(): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_REFUSE, 'Order');

        $command = new Refuse\Command();
        $form = $this->createForm(Refuse\Form::class, $command);

        return $this->render('app/orders/goods/refuse/form.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/refuse/action", name=".refuse.action")
     * @param Request $request
     * @param Refuse\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function refuseAction(Request $request, Refuse\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_REFUSE, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new Refuse\Command();
        $form = $this->createForm(Refuse\Form::class, $command);
        $form->handleRequest($request);
        $command->cols = $request->request->get('cols');

        if ($form->isValid()) {
            try {
                $messages = $handler->handle($command, $manager);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }

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