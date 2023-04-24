<?php


namespace App\Controller\Ticket;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Order\OrderRepository;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\Model\Order\UseCase\Order\CreateSearch;
use App\Model\Ticket\UseCase\ClientTicket\Create;
use App\Model\Ticket\UseCase\ClientTicket\Answer;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\Ticket\ClientTicketFetcher;
use App\ReadModel\Ticket\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\FileUploader;
use App\Service\ManagerSettings;
use DomainException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/client-tickets/tickets", name="client-tickets.tickets")
 */
class ClientTicketsController extends AbstractController
{

    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ClientTicketFetcher $fetcher
     * @param ManagerSettings $settings
     * @param ManagerFetcher $managerFetcher
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, ClientTicketFetcher $fetcher, ManagerSettings $settings, ManagerFetcher $managerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ClientTicket');

        $manager = $managerFetcher->get($this->getUser()->getId());
        $managers = $managerFetcher->assoc();

        $settings = $settings->get('clientTicket');

        $filter = new Filter\ClientTicket\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ClientTicket\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $manager,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/tickets/index.html.twig', [
            'pagination' => $pagination,
            'managers' => $managers,
            'filter' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param ClientTicket $clientTicket
     * @param Request $request
     * @param ManagerFetcher $managerFetcher
     * @param Answer\Handler $handler
     * @param Flusher $flusher
     * @return Response
     */
    public function show(ClientTicket $clientTicket, Request $request, ManagerFetcher $managerFetcher, Answer\Handler $handler, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ClientTicket');

        $manager = $managerFetcher->get($this->getUser()->getId());

        if ($clientTicket->getGroup()) {
            $isAccess = false;
            foreach ($clientTicket->getGroup()->getManagers() as $ticketManager) {
                if ($ticketManager->getId() == $manager->getId()) {
                    $isAccess = true;
                }
            }
            if (!$isAccess) {
                throw new \Symfony\Component\Finder\Exception\AccessDeniedException('Доступ запрещен');
            }
        }

        if (!$clientTicket->isOpened()) {
            $clientTicket->open($manager);
        }
        $clientTicket->read();
        $flusher->flush();

        $command = new Answer\Command();

        $form = $this->createForm(Answer\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('attach')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('user_ticket_attach_directory'));
                    $attachFilename = $fileUploader->upload($attach, true);
                    if ($attachFilename) {
                        $command->attach = $attachFilename;
                    }
                }
                $handler->handle($command, $clientTicket, $manager);
                return $this->redirectToRoute('client-tickets.tickets.show', ['id' => $clientTicket->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

//        dump($clientTicket);

        return $this->render('app/tickets/show.html.twig', [
            'ticket' => $clientTicket,
            'managers' => $managerFetcher->assoc(),
            'form' => $form->createView(),
            'ticket_attach_folder' => '/' . $this->getParameter('user_ticket_attach_www'),
        ]);
    }

    /**
     * @Route("/{id}/user", name=".user")
     * @param ClientTicket $clientTicket
     * @return Response
     */
    public function user(ClientTicket $clientTicket): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ClientTicket');

        return $this->render('app/tickets/ticket/user.html.twig', [
            'ticket' => $clientTicket
        ]);
    }

    /**
     * @Route("/{id}/auto", name=".auto")
     * @param ClientTicket $clientTicket
     * @return Response
     */
    public function auto(ClientTicket $clientTicket): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ClientTicket');

        return $this->render('app/tickets/ticket/auto.html.twig', [
            'ticket' => $clientTicket
        ]);
    }

    /**
     * @Route("/{id}/order", name=".order")
     * @param ClientTicket $clientTicket
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function order(ClientTicket $clientTicket, OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ClientTicket');

        return $this->render('app/tickets/ticket/order.html.twig', [
            'ticket' => $clientTicket
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @return Response
     */
    public function create(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ClientTicket');

        $command = new CreateSearch\Command();
        $form = $this->createForm(CreateSearch\Form::class, $command);

        return $this->render('app/tickets/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/create/search", name=".create.search")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param ManagerFetcher $managerFetcher
     * @param Create\Handler $handler
     * @return Response
     */
    public function createSearch(Request $request, UserRepository $userRepository, ManagerFetcher $managerFetcher, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ClientTicket');

        if ($request->query->get('form')['phonemob']) {
            $phonemob = $request->query->get('form')['phonemob']['phonemob'];
            $user = $userRepository->findOneBy(['phonemob' => $phonemob]);
            if (!$user) {
                $this->addFlash('error', 'Клиент с таким телефоном не найден');
                return $this->redirectToRoute('client-tickets.tickets.create');
            }
        } else {
            $this->addFlash('error', 'Мобильный телефон не введен');
            return $this->redirectToRoute('client-tickets.tickets.create');
        }

        $manager = $managerFetcher->get($this->getUser()->getId());

        $command = new Create\Command($manager);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('attach')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('user_ticket_attach_directory'));
                    $attachFilename = $fileUploader->upload($attach, true);
                    if ($attachFilename) {
                        $command->attach = $attachFilename;
                    }
                }
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('client-tickets.tickets');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/tickets/create_search.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ClientTicket $clientTicket
     * @param Flusher $flusher
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function delete(ClientTicket $clientTicket, Flusher $flusher, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ClientTicket');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $clientTicket->delete($manager);
            $flusher->flush();
            $data['message'] = 'Тикет удален';
//            $data['reload'] = true;
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/close", name=".close")
     * @param ClientTicket $clientTicket
     * @param Flusher $flusher
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function close(ClientTicket $clientTicket, Flusher $flusher, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ClientTicket');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $clientTicket->close($manager);
            $flusher->flush();
            $data['reload'] = true;
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/newTickets", name=".newTickets")
     * @param ClientTicketFetcher $fetcher
     * @param ManagerRepository $managerRepository
     * @return Response
     * @throws Exception
     */
    public function newTickets(ClientTicketFetcher $fetcher, ManagerRepository $managerRepository): Response
    {
        $manager = $managerRepository->get($this->getUser()->getId());
        $tickets = $fetcher->getNewTickets($manager);

        $result = [];

        if ($tickets) {
            foreach ($tickets as $ticket) {
                $diff = (new \DateTime($ticket['dateofanswer']))->diff(new \DateTime());
                $ticket['mins'] = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
                $result[] = $this->renderView('app/tickets/new/item.html.twig', ['ticket' => $ticket]);
            }
        } else {
            $result[] = $this->renderView('app/tickets/new/none.html.twig');
        }

        $data = [
            'count' => count($tickets),
            'result' => $result
        ];

        return $this->json($data);
    }
}