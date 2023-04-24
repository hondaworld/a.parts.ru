<?php


namespace App\Controller\Contact;


use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Contact\UseCase\Contact\Create;
use App\Model\Contact\UseCase\Contact\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\Contact\TownFetcher;
use App\Security\Voter\Manager\ManagerVoter;
use App\Security\Voter\User\UserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users/contacts", name="users.contacts")
 */
class UserContactsController extends AbstractController
{

    /**
     * @Route("/{userID}/", name="")
     * @param User $user
     * @param ContactFetcher $fetcher
     * @return Response
     */
    public function index(User $user, ContactFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_CONTACTS, $user);

        $contacts = $fetcher->allByUser($user);

        return $this->render('app/contacts/users/index.html.twig', [
            'user' => $user,
            'contacts' => $contacts,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{userID}/create", name=".create")
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function create(User $user, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_CONTACTS_CHANGE, $user);

        $command = new Create\Command($user);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.contacts', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/users/create.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param Contact $contact
     * @param Request $request
     * @param Edit\Handler $handler
     * @param TownFetcher $townFetcher
     * @return Response
     */
    public function edit(User $user, Contact $contact, Request $request, Edit\Handler $handler, TownFetcher $townFetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_CONTACTS_CHANGE, $user);

        $command = Edit\Command::fromContact($contact, $townFetcher);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.contacts', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/delete", name=".delete")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param int $id
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(User $user, int $id, Request $request, ContactRepository $contacts, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::USER_CONTACTS_CHANGE, $user);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $contact = $contacts->get($id);
            $contact->clearCashUsers();
            $contact->clearGruzUsers();
//            if ($contact->isMain() == 1) {
//                $data = ['code' => 500, 'message' => 'Невозможно удалить основной контакт'];
//            } else {
            $em->remove($contact);
            $flusher->flush();
            $data['message'] = 'Контакт клиента удален';
//            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{userID}/hide", name=".hide")
     * @param User $user
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(User $user, Request $request, ContactRepository $contacts, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::USER_CONTACTS_CHANGE, $user);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $contact = $contacts->get($request->query->getInt('id'));
            if ($contact->isMain() == 1) {
                $data = ['code' => 500, 'message' => 'Невозможно скрыть основной контакт'];
            } else {
                $contact->hide();
                $flusher->flush();
                $data['action'] = 'hide';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{userID}/unHide", name=".unHide")
     * @param User $user
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(User $user, Request $request, ContactRepository $contacts, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::USER_CONTACTS_CHANGE, $user);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $contact = $contacts->get($request->query->getInt('id'));
            $contact->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}