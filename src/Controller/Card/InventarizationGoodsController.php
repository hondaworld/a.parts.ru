<?php


namespace App\Controller\Card;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Inventarization\Inventarization;
use App\Model\Card\Entity\Inventarization\InventarizationGood;
use App\Model\Card\Entity\Inventarization\InventarizationGoodRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Card\UseCase\Inventarization\CreateGood;
use App\Model\Card\UseCase\Inventarization\Quantity;
use App\Model\Card\UseCase\Inventarization\QuantityScan;
use App\Model\Card\UseCase\Inventarization\ScanSearch;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Card\Filter;
use App\ReadModel\Card\InventarizationGoodFetcher;
use App\ReadModel\Sklad\ZapSkladLocationFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
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
 * @Route("/inventarizations/goods", name="inventarizations.goods")
 */
class InventarizationGoodsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param Inventarization $inventarization
     * @param Request $request
     * @param InventarizationGoodFetcher $fetcher
     * @param ZapSkladLocationFetcher $zapSkladLocationFetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws Exception
     */
    public function index(Inventarization $inventarization, Request $request, InventarizationGoodFetcher $fetcher, ZapSkladLocationFetcher $zapSkladLocationFetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $settings = $settings->get('inventarizationGood');

        $filter = new Filter\InventarizationGood\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\InventarizationGood\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $inventarization,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $zapCards = [];
        foreach ($pagination->getItems() as $item) {
            $zapCards[] = $item['zapCardID'];
        }

        $locations = $zapSkladLocationFetcher->findQuantityInByZapCards($zapCards);

        return $this->render('app/card/inventarizations/goods/index.html.twig', [
            'inventarization' => $inventarization,
            'pagination' => $pagination,
            'locations' => $locations ?? [],
            'filter' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/inventarization", name=".inventarization")
     * @param Inventarization $inventarization
     * @param Request $request
     * @param InventarizationGoodFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function inventarization(Inventarization $inventarization, Request $request, InventarizationGoodFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $settings = $settings->get('inventarizationGood');

        $filter = new Filter\InventarizationZapCard\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\InventarizationZapCard\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->inventarization(
            $inventarization,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/card/inventarizations/inventarization/index.html.twig', [
            'inventarization' => $inventarization,
            'pagination' => $pagination,
            'locations' => $locations ?? [],
            'filter' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/scan/search", name=".scan.search")
     * @param Inventarization $inventarization
     * @return Response
     */
    public function scanSearch(Inventarization $inventarization): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $command = new ScanSearch\Command();
        $form = $this->createForm(ScanSearch\Form::class, $command);


        return $this->render('app/card/inventarizations/scan/scan_search.html.twig', [
            'inventarization' => $inventarization,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{inventarizationID}/{id}/scan", name=".scan")
     * @ParamConverter("inventarization", options={"id" = "inventarizationID"})
     * @param Inventarization $inventarization
     * @param ZapSklad $zapSklad
     * @return Response
     */
    public function scan(Inventarization $inventarization, ZapSklad $zapSklad, Request $request, InventarizationGoodFetcher $inventarizationGoodFetcher, ManagerRepository $managerRepository, QuantityScan\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new QuantityScan\Command();
        $form = $this->createForm(QuantityScan\Form::class, $command);
        $form->handleRequest($request);

        $searchNumber = $request->query->get('number') ? (new DetailNumber($request->query->get('number'))) : null;
        $scan = $request->query->get('scan') ?? 0;

        $good = [];
        if ($searchNumber) {
            $good = $inventarizationGoodFetcher->searchByNumber($inventarization, $searchNumber, $zapSklad);
            if ($good && $scan != 2) return $this->redirectToRoute('inventarizations.goods.scan', ['inventarizationID' => $inventarization->getId(), 'id' => $zapSklad->getId(), 'number' => $searchNumber->getValue(), 'scan' => 2]);
        }

        if (!$good && $searchNumber) {
            $this->addFlash('error', 'Номер не найден');
        }

//
//        $expenseSklads = $fetcher->allShippingAndPacked($zapSklad);
//        $arZapCards = [];
//        $expenseSklad = [];
//
//        foreach ($expenseSklads as $item) {
//            $arZapCards[] = $item['zapCardID'];
//        }
//
//        $locations = $shopLocationFetcher->findByZapCards($arZapCards);
//        $expenses = $fetcher->findByZapCards($zapSklad, $arZapCards);
//
//        foreach ($expenseSklads as &$item) {
//            $quantitySklad = 0;
//            foreach ($expenses[$item['zapCardID']] as $expense) {
//                if ($expense['providerPriceID']) {
//                    $item['location'] .= $expense['provider_price_name'] . ' - ' . $expense['quantity'] . "шт.\n";
//                } else {
//                    $quantitySklad += $expense['quantity'];
//                }
//            }
//            if ($quantitySklad > 0) {
//                $item['location'] .= (isset($locations[$item['zapCardID']][$zapSklad->getId()]) ? $locations[$item['zapCardID']][$zapSklad->getId()]['location'] : 'Склад') . ' - ' . $quantitySklad . "шт.\n";
//            }
//
//            if ($searchNumber != '' && $searchNumber->isEqual(new DetailNumber($item['number'])) && $item['quantity'] != $item['quantityPicking']) {
//                if ($item['quantity'] - $item['quantityPicking'] == 1) {
//                    $command->quantityPicking = 1;
//                } else {
//                    if ($scan != 2) return $this->redirectToRoute('sklads.shipping.scan', ['id' => $zapSklad->getId(), 'number' => $searchNumber->getValue(), 'scan' => 2]);
//                }
//                $expenseSklad = $item;
//            }
//        }
//
//        if (!$expenseSklad && $searchNumber) {
//            $this->addFlash('error', 'Номер не найден');
//        }
//
        if ($good && ($form->isSubmitted() && $form->isValid() || $command->quantity_real)) {
            try {
                $handler->handle($command, $inventarization, $zapSklad, $good, $manager);
                return $this->redirectToRoute('inventarizations.goods.scan', ['inventarizationID' => $inventarization->getId(), 'id' => $zapSklad->getId(), 'scan' => 1]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }
//
//
//        return $this->render('app/sklads/shipping/scan/index.html.twig', [
//            'zapSklad' => $zapSklad,
//            'expenseSklads' => $expenseSklads,
//            'expenseSklad' => $expenseSklad,
//            'locations' => $locations ?? [],
//            'expenses' => $expenses ?? [],
//            'form' => $form->createView(),
//            'searchNumber' => $searchNumber,
//        ]);

        return $this->render('app/card/inventarizations/scan/index.html.twig', [
            'inventarization' => $inventarization,
            'zapSklad' => $zapSklad,
            'form' => $form->createView(),
            'searchNumber' => $searchNumber,
            'good' => $good,
        ]);
    }

    /**
     * @Route("/{id}/create", name=".create")
     * @param Inventarization $inventarization
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param ManagerRepository $managerRepository
     * @param CreateGood\Handler $handler
     * @return Response
     */
    public function create(Inventarization $inventarization, Request $request, ValidatorInterface $validator, ManagerRepository $managerRepository, CreateGood\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $data = ['code' => 200, 'message' => ''];

        $command = new CreateGood\Command();
        $command->quantity_real = $request->request->get('value');
        $command->zapCardID = $request->request->get('zapCardID');
        $command->zapSkladID = $request->request->get('zapSkladID');

        $manager = $managerRepository->get($this->getUser()->getId());

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                $handler->handle($command, $inventarization, $manager);
                $data['delete'] = true;
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
     * @Route("/{inventarizationID}/{id}/edit", name=".edit")
     * @ParamConverter("inventarization", options={"id" = "inventarizationID"})
     * @param Inventarization $inventarization
     * @param InventarizationGood $inventarizationGood
     * @param Request $request
     * @param Quantity\Handler $handler
     * @return Response
     */
    public function edit(Inventarization $inventarization, InventarizationGood $inventarizationGood, Request $request, Quantity\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');

        $command = Quantity\Command::fromEntity($inventarizationGood);

        $form = $this->createForm(Quantity\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('inventarizations.goods', ['id' => $inventarization->getId(), 'page' => $request->getSession()->get('page/inventarizationGood') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/inventarizations/goods/edit.html.twig', [
            'inventarization' => $inventarization,
            'inventarizationGood' => $inventarizationGood,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param InventarizationGoodRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, InventarizationGoodRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Inventarization');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $good = $repository->get($id);
            $em->remove($good);
            $flusher->flush();
            $data['message'] = 'Товар удален из инвентаризации';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}