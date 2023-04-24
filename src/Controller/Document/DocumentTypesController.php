<?php

namespace App\Controller\Document;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Document\UseCase\Type\Create;
use App\Model\Document\UseCase\Type\Edit;
use App\ReadModel\Document\DocumentTypeFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/documents/types", name="documents.types")
 */
class DocumentTypesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param DocumentTypeFetcher $fetcher
     * @return Response
     */
    public function index(DocumentTypeFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'DocumentType');

        $types = $fetcher->all();

        return $this->render('app/documents/types/index.html.twig', [
            'types' => $types,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'DocumentType');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('documents.types');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/types/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param DocumentType $type
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(DocumentType $type, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'DocumentType');

        $command = Edit\Command::fromDocument($type);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('documents.types');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/types/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param DocumentTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, DocumentTypeRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DocumentType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $documentType = $repository->get($id);
            if (count($documentType->getIncomeDocuments()) > 0 || count($documentType->getExpenseDocuments()) > 0 || count($documentType->getExpenseSkladDocuments()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить тип документа, прикрепленный к документам']);
            } else {
                $em->remove($documentType);
                $flusher->flush();
                $data['message'] = 'Тип документа удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param DocumentTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, DocumentTypeRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $documentType = $repository->get($request->query->getInt('id'));
            $documentType->hide();
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
     * @param DocumentTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, DocumentTypeRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $documentType = $repository->get($request->query->getInt('id'));
            $documentType->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
