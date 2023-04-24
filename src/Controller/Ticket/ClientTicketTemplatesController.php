<?php

namespace App\Controller\Ticket;

use App\Model\EntityNotFoundException;
use App\Model\Ticket\Entity\ClientTicketTemplate\ClientTicketTemplate;
use App\Model\Ticket\Entity\ClientTicketTemplate\ClientTicketTemplateRepository;
use App\Model\Ticket\UseCase\ClientTicketTemplate\Edit;
use App\Model\Ticket\UseCase\ClientTicketTemplate\Create;
use App\Model\Flusher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/client-tickets/templates", name="client-tickets.templates")
 */
class ClientTicketTemplatesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ClientTicketTemplateRepository $clientTicketTemplateRepository
     * @return Response
     */
    public function index(ClientTicketTemplateRepository $clientTicketTemplateRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ClientTicketTemplate');

        $all = $clientTicketTemplateRepository->findBy([], ['name' => 'asc']);

        return $this->render('app/tickets/clientTicketTemplates/index.html.twig', [
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ClientTicketTemplate');

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('client-tickets.templates');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/tickets/clientTicketTemplates/create.html.twig', [
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ClientTicketTemplate $clientTicketTemplate
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ClientTicketTemplate $clientTicketTemplate, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ClientTicketTemplate');

        $command = Edit\Command::fromEntity($clientTicketTemplate);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('client-tickets.templates');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/tickets/clientTicketTemplates/edit.html.twig', [
            'form' => $form->createView(),
            'clientTicketTemplate' => $clientTicketTemplate
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ClientTicketTemplate $clientTicketTemplate
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ClientTicketTemplate $clientTicketTemplate, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ClientTicketTemplate');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
//            if (count($site->getOrders()) > 0) {
//                $data = ['code' => 500, 'message' => 'У сайта есть заказы'];
//            } else {
                $em->remove($clientTicketTemplate);
                $flusher->flush();
                $data['message'] = 'Департамент удален';
//            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ClientTicketTemplateRepository $clientTicketTemplateRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ClientTicketTemplateRepository $clientTicketTemplateRepository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firm = $clientTicketTemplateRepository->get($request->query->getInt('id'));
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
     * @param ClientTicketTemplateRepository $clientTicketTemplateRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ClientTicketTemplateRepository $clientTicketTemplateRepository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $firm = $clientTicketTemplateRepository->get($request->query->getInt('id'));
            $firm->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
