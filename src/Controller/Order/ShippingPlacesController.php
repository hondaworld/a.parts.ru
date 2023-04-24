<?php

namespace App\Controller\Order;

use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\Template\TemplateRepository;
use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use App\Model\User\Entity\User\User;
use App\Model\Order\UseCase\ShippingPlace\Edit;
use App\Model\Order\UseCase\ShippingPlace\Create;
use App\Model\Flusher;
use App\ReadModel\Order\ShippingPlaceFetcher;
use App\ReadModel\User\TemplateFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/shipping/places", name="order.shipping.places")
 */
class ShippingPlacesController extends AbstractController
{
//    /**
//     * @Route("/{id}/", name="")
//     * @param User $user
//     * @param ShippingPlaceFetcher $fetcher
//     * @return Response
//     */
//    public function index(User $user, ShippingPlaceFetcher $fetcher): Response
//    {
//        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');
//
//        $templates = $fetcher->allNotShipping($user);
//
//        return $this->render('app/users/templates/index.html.twig', [
//            'templates' => $templates,
//            'templateGroup' => $user
//        ]);
//    }

    /**
     * @Route("/create/{id}", name=".create")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(User $user, Request $request, ExpenseDocumentRepository $expenseDocumentRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $expenseDocument);
                return $this->redirectToRoute('order.pick.scan', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/order/pick/shippingPlaces/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param ShippingPlace $shippingPlace
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(User $user, ShippingPlace $shippingPlace, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');

        $command = Edit\Command::fromEntity($shippingPlace);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('order.pick.scan', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/order/pick/shippingPlaces/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'shippingPlace' => $shippingPlace
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ShippingPlace $shippingPlace
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ShippingPlace $shippingPlace, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($shippingPlace);
            $flusher->flush();
            $data['message'] = 'Место удалено';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
