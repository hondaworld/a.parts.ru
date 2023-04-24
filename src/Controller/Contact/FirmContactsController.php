<?php


namespace App\Controller\Contact;


use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Contact\UseCase\Contact\Create;
use App\Model\Contact\UseCase\Contact\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Firm\Entity\Firm\Firm;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\Contact\TownFetcher;
use App\Security\Voter\Firm\FirmVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/firms/contacts", name="firms.contacts")
 */
class FirmContactsController extends AbstractController
{

    /**
     * @Route("/{firmID}/", name="")
     * @param Firm $firm
     * @param ContactFetcher $fetcher
     * @return Response
     */
    public function index(Firm $firm, ContactFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_CONTACTS, $firm);

        $contacts = $fetcher->allByFirm($firm);

        return $this->render('app/contacts/firms/index.html.twig', [
            'firm' => $firm,
            'contacts' => $contacts,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{firmID}/create", name=".create")
     * @param Firm $firm
     * @param Request $request
     * @return Response
     */
    public function create(Firm $firm, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_CONTACTS_CHANGE, $firm);

        $command = new Create\Command($firm);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.contacts', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/firms/create.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/edit", name=".edit")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param Contact $contact
     * @param Request $request
     * @param Edit\Handler $handler
     * @param TownFetcher $townFetcher
     * @return Response
     */
    public function edit(Firm $firm, Contact $contact, Request $request, Edit\Handler $handler, TownFetcher $townFetcher): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_CONTACTS_CHANGE, $firm);

        $command = Edit\Command::fromContact($contact, $townFetcher);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.contacts', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/contacts/firms/edit.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/delete", name=".delete")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param int $id
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Firm $firm, int $id, Request $request, ContactRepository $contacts, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(FirmVoter::FIRM_CONTACTS_CHANGE, $firm);
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
                $flusher->flush();
                $data['message'] = 'Контакт организации удален';
//            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{firmID}/hide", name=".hide")
     * @param Firm $firm
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Firm $firm, Request $request, ContactRepository $contacts, Flusher $flusher): Response
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
     * @Route("/{firmID}/unHide", name=".unHide")
     * @param Firm $firm
     * @param Request $request
     * @param ContactRepository $contacts
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Firm $firm, Request $request, ContactRepository $contacts, Flusher $flusher): Response
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