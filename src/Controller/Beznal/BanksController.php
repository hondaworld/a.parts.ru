<?php


namespace App\Controller\Beznal;


use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Beznal\Entity\Bank\BankRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Beznal\UseCase\Bank\Create;
use App\Model\Beznal\UseCase\Bank\Edit;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\FirmContr\FirmContrRepository;
use App\ReadModel\Beznal\BankFetcher;
use App\ReadModel\Contact\TownFetcher;
use App\ReadModel\Beznal\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/finance/banks", name="finance.banks")
 */
class BanksController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param BankFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, BankFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Bank');

        $settings = $settings->get('bank');

        $filter = new Filter\Bank\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Bank\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/beznals/banks/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Bank');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('finance.banks', ['page' => $request->getSession()->get('page/bank') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/banks/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Bank $bank
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Bank $bank, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Bank');

        $command = Edit\Command::fromEntity($bank);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('finance.banks', ['page' => $request->getSession()->get('page/bank') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/banks/edit.html.twig', [
            'bank' => $bank,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param BankRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, BankRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Bank');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $bank = $repository->get($id);
            if (count($bank->getBeznals()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить банк, прикрепленный к реквизитам']);
            } else {
                $em->remove($bank);
                $flusher->flush();
                $data['message'] = 'Банк удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param BankRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, BankRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $bank = $repository->get($request->query->getInt('id'));
            $bank->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/unHide", name=".unHide")
     * @param Request $request
     * @param BankRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, BankRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $bank = $repository->get($request->query->getInt('id'));
            $bank->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}