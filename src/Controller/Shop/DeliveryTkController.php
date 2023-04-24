<?php


namespace App\Controller\Shop;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\DeliveryTk\DeliveryTk;
use App\Model\Shop\Entity\DeliveryTk\DeliveryTkRepository;
use App\Model\Shop\UseCase\DeliveryTk\Create;
use App\Model\Shop\UseCase\DeliveryTk\Edit;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Shop\DeliveryTkFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/delivery-tk", name="shop.deliveryTk")
 */
class DeliveryTkController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param DeliveryTkFetcher $fetcher
     * @return Response
     */
    public function index(DeliveryTkFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'DeliveryTk');

        $all = $fetcher->all();

        return $this->render('app/shop/deliveryTk/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'DeliveryTk');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.deliveryTk');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/deliveryTk/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param DeliveryTk $deliveryTk
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(DeliveryTk $deliveryTk, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'DeliveryTk');

        $command = Edit\Command::fromEntity($deliveryTk);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.deliveryTk');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/deliveryTk/edit.html.twig', [
            'form' => $form->createView(),
            'deliveryTk' => $deliveryTk
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param DeliveryTk $deliveryTk
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(DeliveryTk $deliveryTk, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DeliveryTk');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
//        if (count($delivery->getPrices()) > 0) {
//            return $this->json(['code' => 500, 'message' => 'Невозможно удалить поставщика, содержащего прайс-листы']);
//        } else {
            try {
                $em->remove($deliveryTk);
                $flusher->flush();
                $data['message'] = 'ТК отгрузка удалена';

            } catch (EntityNotFoundException $e) {
                return $this->json(['code' => 404, 'message' => $e->getMessage()]);
            }
//        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param DeliveryTkRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, DeliveryTkRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'DeliveryTk');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $deliveryTk = $repository->get($request->query->getInt('id'));
            $deliveryTk->hide();
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
     * @param DeliveryTkRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, DeliveryTkRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'DeliveryTk');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $deliveryTk = $repository->get($request->query->getInt('id'));
            $deliveryTk->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}