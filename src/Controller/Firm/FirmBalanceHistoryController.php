<?php


namespace App\Controller\Firm;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Flusher;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Firm\UseCase\BalanceHistory\Create;
use App\Model\Firm\UseCase\BalanceHistory\Edit;
use App\ReadModel\Firm\FirmBalanceHistoryFetcher;
use App\ReadModel\Firm\Filter;
use App\Security\Voter\Firm\FirmBalanceVoter;
use App\Service\ManagerSettings;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/firms/balance/history", name="firms.balance.history")
 */
class FirmBalanceHistoryController extends AbstractController
{

    /**
     * @Route("/{firmID}/", name="")
     * @param Firm $firm
     * @param Request $request
     * @param FirmBalanceHistoryFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws \Exception
     */
    public function index(Firm $firm, Request $request, FirmBalanceHistoryFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(FirmBalanceVoter::FIRM_BALANCE, 'Firm');

        $settings = $settings->get('firmBalanceHistory');

        $filter = new Filter\FirmBalanceHistory\Filter($firm->getId());
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\FirmBalanceHistory\Form::class, $filter);
        $form->handleRequest($request);

        $isPrint = $request->query->getBoolean('isPrint');

        $pagination = $fetcher->allByFirm(
            $firm,
            $filter,
            $request->query->getInt('page', 1),
            $settings,
            $isPrint
        );

        $balances = $fetcher->balanceByFirm($firm);

        if ($isPrint) {
            return $this->render('app/firms/balanceHistory/print.html.twig', [
                'firm' => $firm,
                'pagination' => $pagination
            ]);
        }

        return $this->render('app/firms/balanceHistory/index.html.twig', [
            'firm' => $firm,
            'pagination' => $pagination,
            'balances' => $balances,
            'filter' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/create", name=".create")
     * @param Firm $firm
     * @param Request $request
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function create(Firm $firm, Request $request, Create\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(FirmBalanceVoter::FIRM_BALANCE_CHANGE, 'Firm');

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $firm, $manager);
                return $this->redirectToRoute('firms.balance.history', ['firmID' => $firm->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/balanceHistory/create.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/edit", name=".edit")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param FirmBalanceHistory $firmBalanceHistory
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Firm $firm, FirmBalanceHistory $firmBalanceHistory, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(FirmBalanceVoter::FIRM_BALANCE_CHANGE, 'Firm');

        $command = Edit\Command::fromEntity($firmBalanceHistory);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.balance.history', ['firmID' => $firm->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/balanceHistory/edit.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/delete", name=".delete")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param FirmBalanceHistory $firmBalanceHistory
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Firm $firm, FirmBalanceHistory $firmBalanceHistory, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(FirmBalanceVoter::FIRM_BALANCE_CHANGE, 'Firm');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($firmBalanceHistory);
            $flusher->flush();
            $data['message'] = 'Запись удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}