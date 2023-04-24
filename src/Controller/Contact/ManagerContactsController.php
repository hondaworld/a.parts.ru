<?php


namespace App\Controller\Contact;


use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Contact\UseCase\Contact\Create;
use App\Model\Contact\UseCase\Contact\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\Contact\TownFetcher;
use App\Security\Voter\Manager\ManagerVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/managers/contacts", name="managers.contacts")
 */
class ManagerContactsController extends AbstractController
{

    /**
     * @Route("/{managerID}/", name="")
     * @param Manager $manager
     * @param ContactFetcher $fetcher
     * @return Response
     */
    public function index(Manager $manager, ContactFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_CONTACTS, $manager);

        $contacts = $fetcher->allByManager($manager);

        return $this->render('app/contacts/managers/index.html.twig', [
            'manager' => $manager,
            'contacts' => $contacts,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{managerID}/create", name=".create")
     * @param Manager $manager
     * @param Request $request
     * @return Response
     */
    public function create(Manager $manager, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_CONTACTS_CHANGE, $manager);

        $command = new Create\Command($manager);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.contacts', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/managers/create.html.twig', [
            'manager' => $manager,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{managerID}/{id}/edit", name=".edit")
     * @ParamConverter("manager", options={"id" = "managerID"})
     * @param Manager $manager
     * @param Contact $contact
     * @param Request $request
     * @param Edit\Handler $handler
     * @param TownFetcher $townFetcher
     * @return Response
     */
    public function edit(Manager $manager, Contact $contact, Request $request, Edit\Handler $handler, TownFetcher $townFetcher): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_CONTACTS_CHANGE, $manager);

        $command = Edit\Command::fromContact($contact, $townFetcher);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.contacts', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/managers/edit.html.twig', [
            'manager' => $manager,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{managerID}/{id}/delete", name=".delete")
     * @ParamConverter("manager", options={"id" = "managerID"})
     * @param Manager $manager
     * @param int $id
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Manager $manager, int $id, Request $request, ContactRepository $contacts, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_CONTACTS_CHANGE, $manager);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $contact = $contacts->get($id);
//            if ($contact->isMain() == 1) {
//                $data = ['code' => 500, 'message' => 'Невозможно удалить основной контакт'];
//            } else {
                $em->remove($contact);
//            $manager->removeContact($contact);
            $flusher->flush();
            $data['message'] = 'Контакт менеджера удален';
//            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{managerID}/hide", name=".hide")
     * @param Manager $manager
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Manager $manager, Request $request, ContactRepository $contacts, Flusher $flusher): Response
    {
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
     * @Route("/{managerID}/unHide", name=".unHide")
     * @param Manager $manager
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Manager $manager, Request $request, ContactRepository $contacts, Flusher $flusher): Response
    {
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