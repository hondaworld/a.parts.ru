<?php


namespace App\Controller\Order;


use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\Order\UseCase\Order\ExpUser;
use App\Model\Order\UseCase\Order\ExpFirm;
use App\Model\Order\UseCase\Order\Cashier;
use App\Model\Order\UseCase\Order\CashierFirmContr;
use App\Model\Order\UseCase\Order\CashierSchetFak;
use App\Model\Order\UseCase\Order\Getter;
use App\Model\Order\UseCase\Order\GetterFirmContr;
use App\Model\Order\UseCase\Order\Sender;
use App\Model\Order\UseCase\Order\DocumentPrefixes;
use App\Model\Order\UseCase\Order\Osn;
use App\ReadModel\Beznal\BeznalFetcher;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\Firm\SchetFetcher;
use App\ReadModel\User\Filter;
use App\Security\Voter\StandartActionsVoter;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/document", name="order.document")
 */
class OrderDocumentController extends AbstractController
{
    /**
     * @Route("/{id}/expUser", name=".expUser")
     * @param User $user
     * @param Request $request
     * @param ExpUser\Handler $handler
     * @param UserRepository $userRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function expUser(User $user, Request $request, ExpUser\Handler $handler, UserRepository $userRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $contacts = [];
        $beznals = [];
        if ($request->get('form') && $request->get('form')['firm']['id']) {
            $userID = ($request->get('form')['firm']['id']);
            $contacts = $contactFetcher->assocAllByUser($userRepository->get($userID));
            $beznals = $beznalFetcher->assocAllByUser($userRepository->get($userID));
        } else if ($expenseDocument->getExpUser()) {
            $contacts = $contactFetcher->assocAllByUser($expenseDocument->getExpUser());
            $beznals = $beznalFetcher->assocAllByUser($expenseDocument->getExpUser());
        }

        $command = ExpUser\Command::fromEntity($expenseDocument, $contacts, $beznals);

        $form = $this->createForm(ExpUser\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'expUser',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/expFirm", name=".expFirm")
     * @param User $user
     * @param Request $request
     * @param ExpFirm\Handler $handler
     * @param FirmRepository $firmRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     * @throws Exception
     */
    public function expFirm(User $user, Request $request, ExpFirm\Handler $handler, FirmRepository $firmRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $contacts = [];
        $beznals = [];
        if ($request->get('form') && $request->get('form')['firm']['id']) {
            $firmID = ($request->get('form')['firm']['id']);
            $contacts = $contactFetcher->assocAllByFirm($firmRepository->get($firmID));
            $beznals = $beznalFetcher->assocAllByFirm($firmRepository->get($firmID));
        } else if ($expenseDocument->getExpFirm()) {
            $contacts = $contactFetcher->assocAllByFirm($expenseDocument->getExpFirm());
            $beznals = $beznalFetcher->assocAllByFirm($expenseDocument->getExpFirm());
        }

        $command = ExpFirm\Command::fromEntity($expenseDocument, $contacts, $beznals);

        $form = $this->createForm(ExpFirm\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'expFirm',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/cashier", name=".cashier")
     * @param User $user
     * @param Request $request
     * @param Cashier\Handler $handler
     * @param UserRepository $userRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function cashier(User $user, Request $request, Cashier\Handler $handler, UserRepository $userRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $contacts = [];
        $beznals = [];
        if ($request->get('form') && $request->get('form')['user']['id']) {
            $cash_userID = ($request->get('form')['user']['id']);
            $contacts = $contactFetcher->assocAllByUser($userRepository->get($cash_userID));
            $beznals = $beznalFetcher->assocAllByUser($userRepository->get($cash_userID));
        } else if ($expenseDocument->getCashUser()) {
            $contacts = $contactFetcher->assocAllByUser($expenseDocument->getCashUser());
            $beznals = $beznalFetcher->assocAllByUser($expenseDocument->getCashUser());
        }

        $command = Cashier\Command::fromEntity($expenseDocument, $contacts, $beznals);

        $form = $this->createForm(Cashier\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'cashier',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/cashier-firmcontr", name=".cashierFirmContr")
     * @param User $user
     * @param Request $request
     * @param CashierFirmContr\Handler $handler
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function cashierFirmContr(User $user, Request $request, CashierFirmContr\Handler $handler, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = CashierFirmContr\Command::fromEntity($expenseDocument);

        $form = $this->createForm(CashierFirmContr\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'cashierFirmContr',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/cashier-schet-fak", name=".cashierSchetFak")
     * @param User $user
     * @param Request $request
     * @param CashierSchetFak\Handler $handler
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function cashierSchetFak(User $user, Request $request, CashierSchetFak\Handler $handler, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = CashierSchetFak\Command::fromEntity($expenseDocument);

        $form = $this->createForm(CashierSchetFak\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'cashierSchetFak',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/getter", name=".getter")
     * @param User $user
     * @param Request $request
     * @param Getter\Handler $handler
     * @param UserRepository $userRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function getter(User $user, Request $request, Getter\Handler $handler, UserRepository $userRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $contacts = [];
        $beznals = [];
        if ($request->get('form') && $request->get('form')['user']['id']) {
            $gruz_userID = ($request->get('form')['user']['id']);
            $contacts = $contactFetcher->assocAllByUser($userRepository->get($gruz_userID));
            $beznals = $beznalFetcher->assocAllByUser($userRepository->get($gruz_userID));
        } else if ($expenseDocument->getGruzUser()) {
            $contacts = $contactFetcher->assocAllByUser($expenseDocument->getGruzUser());
            $beznals = $beznalFetcher->assocAllByUser($expenseDocument->getGruzUser());
        }

        $command = Getter\Command::fromEntity($expenseDocument, $contacts, $beznals);

        $form = $this->createForm(Getter\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'getter',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/getter-firmcontr", name=".getterFirmContr")
     * @param User $user
     * @param Request $request
     * @param GetterFirmContr\Handler $handler
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function getterFirmContr(User $user, Request $request, GetterFirmContr\Handler $handler, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = GetterFirmContr\Command::fromEntity($expenseDocument);

        $form = $this->createForm(GetterFirmContr\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'getterFirmContr',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/sender", name=".sender")
     * @param User $user
     * @param Request $request
     * @param Sender\Handler $handler
     * @param FirmRepository $firmRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     * @throws Exception
     */
    public function sender(User $user, Request $request, Sender\Handler $handler, FirmRepository $firmRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $contacts = [];
        $beznals = [];
        if ($request->get('form') && $request->get('form')['firm']['id']) {
            $firmID = ($request->get('form')['firm']['id']);
            $contacts = $contactFetcher->assocAllByFirm($firmRepository->get($firmID));
            $beznals = $beznalFetcher->assocAllByFirm($firmRepository->get($firmID));
        } else if ($expenseDocument->getGruzFirm()) {
            $contacts = $contactFetcher->assocAllByFirm($expenseDocument->getGruzFirm());
            $beznals = $beznalFetcher->assocAllByFirm($expenseDocument->getGruzFirm());
        }

        $command = Sender\Command::fromEntity($expenseDocument, $contacts, $beznals);

        $form = $this->createForm(Sender\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'expFirm',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/prefixes", name=".prefixes")
     * @param User $user
     * @param Request $request
     * @param DocumentPrefixes\Handler $handler
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function prefixes(User $user, Request $request, DocumentPrefixes\Handler $handler, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = DocumentPrefixes\Command::fromEntity($expenseDocument);

        $form = $this->createForm(DocumentPrefixes\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'prefixes',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/osn", name=".osn")
     * @param User $user
     * @param Request $request
     * @param Osn\Handler $handler
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function osn(User $user, Request $request, Osn\Handler $handler, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = Osn\Command::fromEntity($expenseDocument);

        $form = $this->createForm(Osn\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('orders.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'osn',
            'user' => $user,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/schet", name=".schet")
     * @param User $user
     * @param SchetFetcher $schetFetcher
     * @return Response
     * @throws Exception
     */
    public function schet(User $user, SchetFetcher $schetFetcher): Response
    {
        $all = $schetFetcher->allByUser($user->getId());

        return $this->render('app/orders/order/document/osn/schet.html.twig', [
            'all' => $all
        ]);
    }
}