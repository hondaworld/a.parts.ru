<?php


namespace App\Controller\Card;


use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Card\UseCase\Location\Create;
use App\Model\Card\UseCase\Location\Edit;
use App\Model\Card\UseCase\Location\Perem;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Card\ZapCardReserveFetcher;
use App\ReadModel\Card\ZapCardReserveSkladFetcher;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\ReadModel\Sklad\ZapSkladLocationFetcher;
use App\Security\Voter\StandartActionsVoter;
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
 * @Route("/card/parts/sklad", name="card.parts.sklad")
 */
class ZapSkladLocationsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapCard $zapCard
     * @param ZapSkladLocationFetcher $fetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param ExpenseDocumentFetcher $expenseDocumentFetcher
     * @return Response
     * @throws Exception
     */
    public function index(ZapCard $zapCard, ZapSkladLocationFetcher $fetcher, IncomeFetcher $incomeFetcher, ZapSkladFetcher $zapSkladFetcher, ExpenseDocumentFetcher $expenseDocumentFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');

        $all = $fetcher->allByZapCard($zapCard->getId());
//        $locations = array_map(function($a) {
//            return $a['zapSkladID'];
//        }, $all);

//        $command = new Perem\Command($locations);
//        $form = $this->createForm(Create\Form::class, $command);

        $quantityInWarehouse = $incomeFetcher->findQuantityInWarehouseByZapCard($zapCard->getId());
        $quantities = $incomeFetcher->findAllQuantitiesByZapCard($zapCard->getId());
        $sklads = $zapSkladFetcher->assoc();

        $arr = $expenseDocumentFetcher->expenseForYearByZapCard($zapCard->getId());
        $chartData = [];
        foreach ($arr as $item) {
            $chartData[$item['year']][$item['month']] = $item['quantity'];
        }

        $arr = $expenseDocumentFetcher->expenseForYearByZapCard($zapCard->getId(), 1);
        $chartDataLastYear = [];
        foreach ($arr as $item) {
            $chartDataLastYear[$item['year']][$item['month']] = $item['quantity'];
        }

        $date = new \DateTime();
        $date->setDate(date('Y') - 1, date('m') + 1, 1);
        $chartDate = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartDate[] = [
                'month' => intval($date->format('m')),
                'year' => $date->format('Y'),
            ];
            $date->modify('+1 month');
        }
//        dump($chartData);
//        dump($chartDataLastYear);
//        dump($chartDate);

        return $this->render('app/card/location/index.html.twig', [
            'all' => $all,
            'zapCard' => $zapCard,
            'quantityInWarehouse' => $quantityInWarehouse,
            'quantities' => $quantities,
            'sklads' => $sklads,
            'chartData' => $chartData,
            'chartDataLastYear' => $chartDataLastYear,
            'chartDate' => $chartDate,
        ]);
    }

    /**
     * @Route("/{zapCardID}/{id}/perem", name=".perem")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param Perem\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function perem(ZapCard $zapCard, ZapSklad $zapSklad, Request $request, Perem\Handler $handler, ManagerRepository $managerRepository, ValidatorInterface $validator): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new Perem\Command($zapCard, $zapSklad);
        $command->quantity = $request->request->get('value');
        $command->zapSkladID_to = $request->request->get('zapSkladID_to');

        $manager = $managerRepository->get($this->getUser()->getId());

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                $handler->handle($command, $manager);
                $data['reload'] = 1;
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
     * @Route("/{zapCardID}/{id}/reserve", name=".reserve")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @param ZapCardReserveFetcher $zapCardReserveFetcher
     * @param ZapCardReserveSkladFetcher $zapCardReserveSkladFetcher
     * @return Response
     */
    public function reserve(ZapCard $zapCard, ZapSklad $zapSklad, ZapCardReserveFetcher $zapCardReserveFetcher, ZapCardReserveSkladFetcher $zapCardReserveSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');

        $all = $zapCardReserveFetcher->allByZapCardAndZapSklad($zapCard->getId(), $zapSklad->getId());
        $allSklad = $zapCardReserveSkladFetcher->allByZapCardAndZapSklad($zapCard->getId(), $zapSklad->getId());

        return $this->render('app/card/location/reserve/index.html.twig', [
            'all' => $all,
            'allSklad' => $allSklad
        ]);
    }

    /**
     * @Route("/{id}/create", name=".create")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(ZapCard $zapCard, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        $command = new Create\Command($zapCard);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.sklad', ['id' => $zapCard->getId()]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/location/create.html.twig', [
            'form' => $form->createView(),
            'zapCard' => $zapCard
        ]);
    }

    /**
     * @Route("/{zapCardID}/{id}/edit", name=".edit")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapSkladLocation $zapSkladLocation
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ZapCard $zapCard, ZapSkladLocation $zapSkladLocation, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        $command = Edit\Command::fromEntity($zapSkladLocation);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts.sklad', ['id' => $zapCard->getId()]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/location/edit.html.twig', [
            'form' => $form->createView(),
            'zapCard' => $zapCard,
            'zapSkladLocation' => $zapSkladLocation
        ]);
    }

    /**
     * @Route("/{zapCardID}/{id}/delete", name=".delete")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapSkladLocation $zapSkladLocation
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapSkladLocation $zapSkladLocation, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($zapSkladLocation);
            $flusher->flush();
            $data['message'] = 'Склад удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}