<?php

namespace App\Controller\Ticket;

use App\Model\EntityNotFoundException;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroupRepository;
use App\Model\Ticket\UseCase\ClientTicketGroup\Edit;
use App\Model\Ticket\UseCase\ClientTicketGroup\Create;
use App\Model\Flusher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/client-tickets/groups", name="client-tickets.groups")
 */
class ClientTicketGroupsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ClientTicketGroupRepository $clientTicketGroupRepository
     * @return Response
     */
    public function index(ClientTicketGroupRepository $clientTicketGroupRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ClientTicketGroup');

        $all = $clientTicketGroupRepository->findBy([], ['name' => 'asc']);

        return $this->render('app/tickets/clientTicketGroups/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ClientTicketGroup');

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('client-tickets.groups');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/tickets/clientTicketGroups/create.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ClientTicketGroup $clientTicketGroup
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ClientTicketGroup $clientTicketGroup, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ClientTicketGroup');

        $command = Edit\Command::fromEntity($clientTicketGroup);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('client-tickets.groups');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/tickets/clientTicketGroups/edit.html.twig', [
            'form' => $form->createView(),
            'clientTicketGroup' => $clientTicketGroup
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ClientTicketGroup $clientTicketGroup
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ClientTicketGroup $clientTicketGroup, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ClientTicketGroup');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($clientTicketGroup->getTickets()) > 0) {
                $data = ['code' => 500, 'message' => 'У департамента есть тикеты'];
            } else {
                $em->remove($clientTicketGroup);
                $flusher->flush();
                $data['message'] = 'Департамент удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ClientTicketGroupRepository $clientTicketGroupRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ClientTicketGroupRepository $clientTicketGroupRepository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firm = $clientTicketGroupRepository->get($request->query->getInt('id'));
            $firm->hide();
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
     * @param ClientTicketGroupRepository $clientTicketGroupRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ClientTicketGroupRepository $clientTicketGroupRepository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firm = $clientTicketGroupRepository->get($request->query->getInt('id'));
            $firm->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
