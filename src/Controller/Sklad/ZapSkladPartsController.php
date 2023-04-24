<?php

namespace App\Controller\Sklad;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Card\ZapCardAbcFetcher;
use App\ReadModel\Expense\ExpenseSkladFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Sklad\ZapCardFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\Sklad\SkladVoter;
use App\Model\Sklad\UseCase\Parts\QuantityMin;
use App\Model\Sklad\UseCase\Parts\QuantityMax;
use App\Model\Sklad\UseCase\Parts\Location;
use App\Model\Card\UseCase\Location\Perem;
use App\Service\ManagerSettings;
use App\ReadModel\Sklad\Filter;
use Doctrine\DBAL\Exception;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/sklads/parts", name="sklads.parts")
 */
class ZapSkladPartsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ZapCardFetcher $fetcher
     * @param ManagerSettings $settings
     * @param OptRepository $optRepository
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardRepository $zapCardRepository
     * @param ExpenseSkladFetcher $expenseSkladFetcher
     * @param ZapCardAbcFetcher $zapCardAbcFetcher
     * @return Response
     * @throws Exception
     */
    public function index(
        ZapSklad            $zapSklad,
        Request             $request,
        ZapCardFetcher      $fetcher,
        ManagerSettings     $settings,
        OptRepository       $optRepository,
        ZapSkladFetcher     $zapSkladFetcher,
        IncomeFetcher       $incomeFetcher,
        ZapCardPriceService $zapCardPriceService,
        ZapCardRepository   $zapCardRepository,
        ExpenseSkladFetcher $expenseSkladFetcher,
        ZapCardAbcFetcher   $zapCardAbcFetcher
    ): Response
    {
        $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PARTS, 'ZapSklad');

        $settings = $settings->get('skladZapCards');

        $filter = new Filter\ZapCard\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;
        $filter->zapSkladID = $zapSklad->getId();

        $form = $this->createForm(Filter\ZapCard\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $zapSklad,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $opt = $optRepository->get(Opt::DEFAULT_OPT_ID);
        $sklads = $zapSkladFetcher->assoc();
        $zapCardsID = [];

        $items = $pagination->getItems();
        foreach ($items as $item) {
            $zapCardsID[] = $item['zapCardID'];
        }
        $zapCards = $zapCardRepository->findByZapCards($zapCardsID);
        foreach ($items as &$item) {
            $optPrice = $zapCardPriceService->priceOpt($zapCards[$item['zapCardID']], $opt);
            $item['optPrice'] = $optPrice;
        }
        $pagination->setItems($items);

        $quantityInWarehouse = $incomeFetcher->findQuantityInWarehouseByZapCards($zapCardsID);
        $quantityIncome = $incomeFetcher->findQuantityOrderedByZapCards($zapCardsID);
        $quantityFrom = $expenseSkladFetcher->findQuantityOrderedFromSkladsByZapCards($zapCardsID);
        $quantityTo = $expenseSkladFetcher->findQuantityOrderedToSkladsByZapCards($zapCardsID);
        $abc = $zapCardAbcFetcher->assocByZapCardsAndZapSklad($zapCardsID, $zapSklad->getId());

        return $this->render('app/sklads/parts/index.html.twig', [
            'pagination' => $pagination,
            'zapSklad' => $zapSklad,
            'opt' => $opt,
            'sklads' => $sklads,
            'quantityInWarehouse' => $quantityInWarehouse,
            'quantityIncome' => $quantityIncome,
            'quantityFrom' => $quantityFrom,
            'quantityTo' => $quantityTo,
            'abc' => $abc,
            'filter' => $form->createView(),
            'table_sortable' => true,
        ]);
    }

    /**
     * @Route("/{id}/quantityMin", name=".quantityMin")
     * @param ZapSkladLocation $zapSkladLocation
     * @param Request $request
     * @param QuantityMin\Handler $handler
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function quantityMin(ZapSkladLocation $zapSkladLocation, Request $request, QuantityMin\Handler $handler, ValidatorInterface $validator): Response
    {
        try {
            $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PARTS, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = QuantityMin\Command::fromEntity($zapSkladLocation);
        $command->quantityMin = $request->request->get('value');
        if ($command->quantityMin == '') $command->quantityMin = 0;

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                $handler->handle($command);

//                $data['inputIdentification'] = [
//                    ['value' => $zapSkladLocation->getId(), 'name' => 'id'],
//                ];
//                $data['ident'] = 'quantityMin';


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
     * @Route("/{id}/quantityMax", name=".quantityMax")
     * @param ZapSkladLocation $zapSkladLocation
     * @param Request $request
     * @param QuantityMax\Handler $handler
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function quantityMax(ZapSkladLocation $zapSkladLocation, Request $request, QuantityMax\Handler $handler, ValidatorInterface $validator): Response
    {
        try {
            $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PARTS, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = QuantityMax\Command::fromEntity($zapSkladLocation);
        $command->quantityMax = $request->request->get('value');
        if ($command->quantityMax == '') $command->quantityMax = 0;

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
     * @Route("/{zapCardID}/{id}/peremTo", name=".peremTo")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param Perem\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param ValidatorInterface $validator
     * @param ZapSkladRepository $zapSkladRepository
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ExpenseSkladFetcher $expenseSkladFetcher
     * @return Response
     */
    public function peremTo(
        ZapCard             $zapCard,
        ZapSklad            $zapSklad,
        Request             $request,
        Perem\Handler       $handler,
        ManagerRepository   $managerRepository,
        ValidatorInterface  $validator,
        ZapSkladRepository  $zapSkladRepository,
        ZapSkladFetcher     $zapSkladFetcher,
        IncomeFetcher       $incomeFetcher,
        ExpenseSkladFetcher $expenseSkladFetcher
    ): Response
    {
        try {
            $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PARTS, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new Perem\Command($zapCard, $zapSklad);
        $command->quantity = $request->request->get('value');
        $command->zapSkladID_to = $request->request->get('zapSkladID_to');
        $zapSklad_to = $zapSkladRepository->get($command->zapSkladID_to);

        $manager = $managerRepository->get($this->getUser()->getId());

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                try {
                    $handler->handle($command, $manager);

                    $sklads = $zapSkladFetcher->assoc();
                    $quantityInWarehouse = $incomeFetcher->findQuantityInWarehouseByZapCards([$zapCard->getId()]);
                    $quantityIncome = $incomeFetcher->findQuantityOrderedByZapCards([$zapCard->getId()]);
                    $quantityFrom = $expenseSkladFetcher->findQuantityOrderedFromSkladsByZapCards([$zapCard->getId()]);
                    $quantityTo = $expenseSkladFetcher->findQuantityOrderedToSkladsByZapCards([$zapCard->getId()]);

                    $data['idIdentification'] = [];
                    foreach ($sklads as $zapSkladID => $skladName) {
                        $value = $quantityInWarehouse[$zapCard->getId()][$zapSkladID] ?? 0;
                        if (isset($quantityTo[$zapCard->getId()][$zapSkladID])) {
                            $value .= ' (+' . $quantityTo[$zapCard->getId()][$zapSkladID] . ')';
                        }
                        if (isset($quantityFrom[$zapCard->getId()][$zapSkladID])) {
                            $value .= ' (-' . $quantityFrom[$zapCard->getId()][$zapSkladID] . ')';
                        }
                        if (isset($quantityIncome[$zapCard->getId()][$zapSkladID])) {
                            $value .= ' [+' . $quantityIncome[$zapCard->getId()][$zapSkladID] . ']';
                        }
                        $data['idIdentification'][] = ['value' => $value, 'name' => 'quantity_' . $zapCard->getId() . '_' . $zapSkladID];
                    }

                    $data['message'] = 'Деталь ' . $zapCard->getNumber()->getValue() . ' отправлена в перемещение со склада ' . $zapSklad->getNameShort() . ' на ' . $zapSklad_to->getNameShort();
                } catch (DomainException $e) {
                    $data['code'] = 404;
                    $data['message'] = $e->getMessage();
                }
            } catch (Exception $e) {
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
     * @Route("/{zapCardID}/{id}/peremFrom", name=".peremFrom")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad_to
     * @param Request $request
     * @param Perem\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param ValidatorInterface $validator
     * @param ZapSkladRepository $zapSkladRepository
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ExpenseSkladFetcher $expenseSkladFetcher
     * @return Response
     */
    public function peremFrom(
        ZapCard             $zapCard,
        ZapSklad            $zapSklad_to,
        Request             $request,
        Perem\Handler       $handler,
        ManagerRepository   $managerRepository,
        ValidatorInterface  $validator,
        ZapSkladRepository  $zapSkladRepository,
        ZapSkladFetcher     $zapSkladFetcher,
        IncomeFetcher       $incomeFetcher,
        ExpenseSkladFetcher $expenseSkladFetcher
    ): Response
    {
        try {
            $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PARTS, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $zapSklad = $zapSkladRepository->get($request->request->get('zapSkladID_to'));

        $command = new Perem\Command($zapCard, $zapSklad);
        $command->quantity = $request->request->get('value');
        $command->zapSkladID_to = $zapSklad_to->getId();

        $manager = $managerRepository->get($this->getUser()->getId());

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                try {
                    $handler->handle($command, $manager);

                    $sklads = $zapSkladFetcher->assoc();
                    $quantityInWarehouse = $incomeFetcher->findQuantityInWarehouseByZapCards([$zapCard->getId()]);
                    $quantityIncome = $incomeFetcher->findQuantityOrderedByZapCards([$zapCard->getId()]);
                    $quantityFrom = $expenseSkladFetcher->findQuantityOrderedFromSkladsByZapCards([$zapCard->getId()]);
                    $quantityTo = $expenseSkladFetcher->findQuantityOrderedToSkladsByZapCards([$zapCard->getId()]);

                    $data['idIdentification'] = [];
                    foreach ($sklads as $zapSkladID => $skladName) {
                        $value = $quantityInWarehouse[$zapCard->getId()][$zapSkladID] ?? 0;
                        if (isset($quantityTo[$zapCard->getId()][$zapSkladID])) {
                            $value .= ' (+' . $quantityTo[$zapCard->getId()][$zapSkladID] . ')';
                        }
                        if (isset($quantityFrom[$zapCard->getId()][$zapSkladID])) {
                            $value .= ' (-' . $quantityFrom[$zapCard->getId()][$zapSkladID] . ')';
                        }
                        if (isset($quantityIncome[$zapCard->getId()][$zapSkladID])) {
                            $value .= ' [+' . $quantityIncome[$zapCard->getId()][$zapSkladID] . ']';
                        }
                        $data['idIdentification'][] = ['value' => $value, 'name' => 'quantity_' . $zapCard->getId() . '_' . $zapSkladID];
                    }

                    $data['message'] = 'Деталь ' . $zapCard->getNumber()->getValue() . ' отправлена в перемещение со склада ' . $zapSklad->getNameShort() . ' на ' . $zapSklad_to->getNameShort();
                } catch (DomainException $e) {
                    $data['code'] = 404;
                    $data['message'] = $e->getMessage();
                }
            } catch (Exception $e) {
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
     * @Route("/{id}/scan", name=".scan")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ZapCardRepository $zapCardRepository
     * @param IncomeFetcher $incomeFetcher
     * @return Response
     * @throws Exception
     */
    public function scan(ZapSklad $zapSklad, Request $request, ZapCardRepository $zapCardRepository, IncomeFetcher $incomeFetcher): Response
    {
        try {
            $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PARTS, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $arr = [];
        $searchNumber = $request->query->get('number') ? (new DetailNumber($request->query->get('number'))) : '';

        if ($searchNumber != '') {
            $zapCards = $zapCardRepository->findByNumber($searchNumber);

            foreach ($zapCards as $zapCard) {

                $quantity = $incomeFetcher->getQuantityByZapCardAndZapSklad($zapCard->getId(), $zapSklad->getId());

                $qCommand = new Location\Command();
                if ($request->query->get('location_' . $zapCard->getId())) {
                    $qCommand->location = $request->query->get('location_' . $zapCard->getId());
                } elseif ($zapCard->getLocationByZapSklad($zapSklad) && $zapCard->getLocationByZapSklad($zapSklad)->getLocation()) {
                    $qCommand->location = $zapCard->getLocationByZapSklad($zapSklad)->getLocation()->getName();
                }
                $formQ = $this->createForm(Location\Form::class, $qCommand);

                $arr[$zapCard->getId()] = [
                    'zapCard' => $zapCard,
                    'quantity' => $quantity,
                    'form' => $formQ->createView()
                ];
            }
        }

        return $this->render('app/sklads/parts/scan/index.html.twig', [
            'zapSklad' => $zapSklad,
            'arr' => $arr,
            'searchNumber' => $searchNumber == '' ? '' : $searchNumber->getValue(),
        ]);
    }

    /**
     * @Route("/{zapCardID}/{id}/locationUpdate", name=".locationUpdate")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param Location\Handler $handler
     * @return Response
     */
    public function locationUpdate(ZapCard $zapCard, ZapSklad $zapSklad, Request $request, Location\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PARTS, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $command = new Location\Command();
        $form = $this->createForm(Location\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $zapCard, $zapSklad);
                return $this->redirectToRoute('sklads.parts.scan', ['id' => $zapSklad->getId(), 'scan' => 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('sklads.parts.scan', ['id' => $zapSklad->getId(), 'location_' . $zapCard->getId() => $command->location, 'number' => $request->query->get('searchNumber') ?? '']);
    }
}