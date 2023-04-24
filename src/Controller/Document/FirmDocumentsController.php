<?php


namespace App\Controller\Document;


use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\Document\UseCase\Document\Create;
use App\Model\Document\UseCase\Document\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Firm\Entity\Firm\Firm;
use App\ReadModel\Document\DocumentFetcher;
use App\Security\Voter\Firm\FirmVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/firms/documents", name="firms.documents")
 */
class FirmDocumentsController extends AbstractController
{

    /**
     * @Route("/{firmID}/", name="")
     * @param Firm $firm
     * @param DocumentFetcher $fetcher
     * @return Response
     */
    public function index(Firm $firm, DocumentFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_DOCUMENTS, $firm);

        $documents = $fetcher->allByFirm($firm);

        return $this->render('app/documents/firms/index.html.twig', [
            'firm' => $firm,
            'documents' => $documents,
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
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_DOCUMENTS_CHANGE, $firm);

        $command = new Create\Command($firm);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.documents', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/firms/create.html.twig', [
            'firm' => $firm,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{firmID}/{id}/edit", name=".edit")
     * @ParamConverter("firm", options={"id" = "firmID"})
     * @param Firm $firm
     * @param Document $document
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Firm $firm, Document $document, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(FirmVoter::FIRM_DOCUMENTS_CHANGE, $firm);

        $command = Edit\Command::fromDocument($document);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('firms.documents', ['firmID' => $firm->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/firms/edit.html.twig', [
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
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Firm $firm, int $id, Request $request, DocumentRepository $documents, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(FirmVoter::FIRM_DOCUMENTS_CHANGE, $firm);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $document = $documents->get($id);
            $em->remove($document);
            $flusher->flush();
            $data['message'] = 'Документ организации удален';
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{firmID}/hide", name=".hide")
     * @param Firm $firm
     * @param Request $request
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Firm $firm, Request $request, DocumentRepository $documents, Flusher $flusher): Response
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
     * @Route("/{firmID}/unHide", name=".unHide")
     * @param Firm $firm
     * @param Request $request
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Firm $firm, Request $request, DocumentRepository $documents, Flusher $flusher): Response
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