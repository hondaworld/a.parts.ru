<?php


namespace App\Controller\User;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\User\UseCase\FirmContr\Create;
use App\Model\User\UseCase\FirmContr\Edit;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\FirmContr\FirmContrRepository;
use App\ReadModel\Beznal\BankFetcher;
use App\ReadModel\Contact\TownFetcher;
use App\ReadModel\User\Filter;
use App\ReadModel\User\FirmContrFetcher;
use App\Security\Voter\Firm\FirmVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/firmcontr", name="firmcontr")
 */
class FirmContrController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param FirmContrFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, FirmContrFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'FirmContr');

        $settings = $settings->get('firmContr');

        $filter = new Filter\FirmContr\Filter();
        $filter->inPage = isset($settings['inPage']) ? $settings['inPage'] : $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\FirmContr\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/users/firmContr/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @param TownFetcher $townFetcher
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'FirmContr');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firmcontr', ['page' => $request->getSession()->get('page/firmContr') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/firmContr/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param FirmContr $firmContr
     * @param Request $request
     * @param Edit\Handler $handler
     * @param TownFetcher $townFetcher
     * @param BankFetcher $bankFetcher
     * @return Response
     */
    public function edit(FirmContr $firmContr, Request $request, Edit\Handler $handler, TownFetcher $townFetcher, BankFetcher $bankFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'FirmContr');

        $command = Edit\Command::fromEntity($firmContr, $townFetcher, $bankFetcher);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firmcontr', ['page' => $request->getSession()->get('page/firmContr') ?: 1]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/firmContr/edit.html.twig', [
            'firmcontr' => $firmContr,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param FirmContrRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, FirmContrRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'FirmContr');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $firmContr = $repository->get($id);
            $firmContr->clearCashUsers();
            $firmContr->clearGruzUsers();
            $em->remove($firmContr);
            $flusher->flush();
            $data['message'] = 'Организация удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param FirmContrRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, FirmContrRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firmContr = $repository->get($request->query->getInt('id'));
            $firmContr->hide();
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
     * @param FirmContrRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, FirmContrRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firmContr = $repository->get($request->query->getInt('id'));
            $firmContr->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}