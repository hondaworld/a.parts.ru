<?php


namespace App\Controller\Document;


use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\Document\UseCase\Document\Create;
use App\Model\Document\UseCase\Document\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Document\DocumentFetcher;
use App\Security\Voter\Manager\ManagerVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/managers/documents", name="managers.documents")
 */
class ManagerDocumentsController extends AbstractController
{

    /**
     * @Route("/{managerID}/", name="")
     * @param Manager $manager
     * @param DocumentFetcher $fetcher
     * @return Response
     */
    public function index(Manager $manager, DocumentFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_DOCUMENTS, $manager);

        $documents = $fetcher->allByManager($manager);

        return $this->render('app/documents/managers/index.html.twig', [
            'manager' => $manager,
            'documents' => $documents,
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
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_DOCUMENTS_CHANGE, $manager);

        $command = new Create\Command($manager);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.documents', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/managers/create.html.twig', [
            'manager' => $manager,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{managerID}/{id}/edit", name=".edit")
     * @ParamConverter("manager", options={"id" = "managerID"})
     * @param Manager $manager
     * @param Document $document
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Manager $manager, Document $document, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_DOCUMENTS_CHANGE, $manager);

        $command = Edit\Command::fromDocument($document);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('managers.documents', ['managerID' => $manager->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/managers/edit.html.twig', [
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
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Manager $manager, int $id, Request $request, DocumentRepository $documents, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ManagerVoter::MANAGER_DOCUMENTS_CHANGE, $manager);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $document = $documents->get($id);
            $em->remove($document);
            $flusher->flush();
            $data['message'] = 'Документ менеджера удален';
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{managerID}/hide", name=".hide")
     * @param Manager $manager
     * @param Request $request
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Manager $manager, Request $request, DocumentRepository $documents, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $document = $documents->get($request->query->getInt('id'));
            $document->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{managerID}/unHide", name=".unHide")
     * @param Manager $manager
     * @param Request $request
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Manager $manager, Request $request, DocumentRepository $documents, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $document = $documents->get($request->query->getInt('id'));
            $document->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}