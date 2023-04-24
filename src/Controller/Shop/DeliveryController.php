<?php


namespace App\Controller\Shop;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\Delivery\Delivery;
use App\Model\Shop\Entity\Delivery\DeliveryRepository;
use App\Model\Shop\UseCase\Delivery\Create;
use App\Model\Shop\UseCase\Delivery\Edit;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Shop\DeliveryFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ModelSorter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/delivery", name="shop.delivery")
 */
class DeliveryController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param DeliveryFetcher $fetcher
     * @return Response
     */
    public function index(DeliveryFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Delivery');

        $all = $fetcher->all();

        return $this->render('app/shop/delivery/index.html.twig', [
            'all' => $all,
            'table_checkable' => true,
            'table_sortable' => true,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Delivery');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.delivery');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/delivery/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Delivery $delivery
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Delivery $delivery, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Delivery');

        $command = Edit\Command::fromEntity($delivery);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.delivery');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/delivery/edit.html.twig', [
            'form' => $form->createView(),
            'delivery' => $delivery
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Delivery $delivery
     * @param DeliveryRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Delivery $delivery, DeliveryRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Delivery');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
//        if (count($delivery->getPrices()) > 0) {
//            return $this->json(['code' => 500, 'message' => 'Невозможно удалить поставщика, содержащего прайс-листы']);
//        } else {
            try {
                $repository->removeSort($delivery->getNumber());
                $em->remove($delivery);
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
     * @param DeliveryRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, DeliveryRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'Delivery');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $delivery = $repository->get($request->query->getInt('id'));
            $delivery->hide();
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
     * @param DeliveryRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, DeliveryRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'Delivery');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $delivery = $repository->get($request->query->getInt('id'));
            $delivery->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param DeliveryRepository $repository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     */
    public function sort(int $id, DeliveryRepository $repository, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $opt = $repository->get($id);

            $oldSort = $opt->getNumber();
            $newSort = $sorter->getNewSort($opt->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $repository->removeSort($oldSort);
                $repository->addSort($newSort);

                $opt->changeNumber($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}