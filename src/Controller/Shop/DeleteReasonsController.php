<?php


namespace App\Controller\Shop;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use App\Model\Shop\Entity\DeleteReason\DeleteReasonRepository;
use App\Model\Shop\UseCase\DeleteReason\Create;
use App\Model\Shop\UseCase\DeleteReason\Edit;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Shop\DeleteReasonFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/delete-reasons", name="shop.deleteReasons")
 */
class DeleteReasonsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param DeleteReasonFetcher $fetcher
     * @return Response
     */
    public function index(DeleteReasonFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'DeleteReason');

        $all = $fetcher->all();

        return $this->render('app/shop/deleteReasons/index.html.twig', [
            'all' => $all,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'DeleteReason');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.deleteReasons');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/deleteReasons/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param DeleteReason $deleteReason
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(DeleteReason $deleteReason, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'DeleteReason');

        $command = Edit\Command::fromEntity($deleteReason);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.deleteReasons');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/deleteReasons/edit.html.twig', [
            'form' => $form->createView(),
            'deleteReason' => $deleteReason
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param DeleteReason $deleteReason
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(DeleteReason $deleteReason, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DeleteReason');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
//        if (count($delivery->getPrices()) > 0) {
//            return $this->json(['code' => 500, 'message' => 'Невозможно удалить поставщика, содержащего прайс-листы']);
//        } else {
            try {
                $em->remove($deleteReason);
                $flusher->flush();
                $data['message'] = 'Доставка удалена';

            } catch (EntityNotFoundException $e) {
                return $this->json(['code' => 404, 'message' => $e->getMessage()]);
            }
//        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param DeleteReasonRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, DeleteReasonRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'DeleteReason');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $deleteReason = $repository->get($request->query->getInt('id'));
            $deleteReason->hide();
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
     * @param DeleteReasonRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, DeleteReasonRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'DeleteReason');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $deleteReason = $repository->get($request->query->getInt('id'));
            $deleteReason->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}