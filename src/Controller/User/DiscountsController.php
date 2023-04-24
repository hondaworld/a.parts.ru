<?php

namespace App\Controller\User;

use App\Model\EntityNotFoundException;
use App\Model\Shop\Entity\Discount\Discount;
use App\Model\Shop\Entity\Discount\DiscountRepository;
use App\Model\User\UseCase\Discount\Edit;
use App\Model\User\UseCase\Discount\Create;
use App\Model\Flusher;
use App\ReadModel\User\DiscountFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/discounts", name="discounts")
 */
class DiscountsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param DiscountFetcher $fetcher
     * @return Response
     */
    public function index(DiscountFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Discount');

        $discounts = $fetcher->all();

        return $this->render('app/users/discounts/index.html.twig', [
            'discounts' => $discounts
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Discount');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('discounts');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/discounts/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Discount $discount
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Discount $discount, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Discount');

        $command = Edit\Command::fromEntity($discount);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('discounts');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/discounts/edit.html.twig', [
            'form' => $form->createView(),
            'discount' => $discount
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param DiscountRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, DiscountRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Discount');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $discount = $repository->get($id);

            $em->remove($discount);
            $flusher->flush();
            $data['message'] = 'Скидка удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
