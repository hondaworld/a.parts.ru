<?php


namespace App\Controller\Finance;


use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Finance\Entity\FinanceType\FinanceTypeRepository;
use App\Model\Finance\UseCase\FinanceType\Create;
use App\Model\Finance\UseCase\FinanceType\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\ReadModel\Contact\TownFetcher;
use App\ReadModel\Finance\FinanceTypeFetcher;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/finance/types", name="finance.types")
 */
class FinanceTypesController extends AbstractController
{

    /**
     * @Route("/", name="")
     * @param FinanceTypeFetcher $fetcher
     * @return Response
     */
    public function index(FinanceTypeFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'FinanceType');

        $financeTypes = $fetcher->all();

        return $this->render('app/finance/financeType/index.html.twig', [
            'financeTypes' => $financeTypes,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'FinanceType');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('finance.types');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/financeType/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param FinanceType $financeType
     * @param Request $request
     * @param Edit\Handler $handler
     * @param TownFetcher $townFetcher
     * @return Response
     */
    public function edit(FinanceType $financeType, Request $request, Edit\Handler $handler, TownFetcher $townFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'FinanceType');

        $command = Edit\Command::fromEntity($financeType);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('finance.types');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/finance/financeType/edit.html.twig', [
            'financeType' => $financeType,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param FinanceTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, Request $request, FinanceTypeRepository $repository, Flusher $flusher): Response
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->json(['code' => 403, 'message' => 'Доступ разрешен только супер администратору']);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $financeType = $repository->get($id);
            $em->remove($financeType);
            $flusher->flush();
            $data['message'] = 'Вид оплаты удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param FinanceTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, FinanceTypeRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'FinanceType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $financeType = $repository->get($request->query->getInt('id'));
            if ($financeType->isMain() == 1) {
                $data = ['code' => 500, 'message' => 'Невозможно скрыть основной контакт'];
            } else {
                $financeType->hide();
                $flusher->flush();
                $data['action'] = 'hide';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/unHide", name=".unHide")
     * @param Request $request
     * @param FinanceTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, FinanceTypeRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'FinanceType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $financeType = $repository->get($request->query->getInt('id'));
            $financeType->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}