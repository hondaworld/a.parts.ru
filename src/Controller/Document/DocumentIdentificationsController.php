<?php

namespace App\Controller\Document;

use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Document\Entity\Identification\DocumentIdentificationRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\Model\Document\UseCase\Identification\Create;
use App\Model\Document\UseCase\Identification\Edit;
use App\ReadModel\Document\DocumentIdentificationFetcher;
use App\ReadModel\Manager\ManagerGroupFetcher;
use App\ReadModel\Menu\MenuActionFetcher;
use \App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/documents/identifications", name="documents.identifications")
 */
class DocumentIdentificationsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param DocumentIdentificationFetcher $fetcher
     * @return Response
     */
    public function index(DocumentIdentificationFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'DocumentIdentification');

        $identifications = $fetcher->all();

        return $this->render('app/documents/identifications/index.html.twig', [
            'identifications' => $identifications,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'DocumentIdentification');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('documents.identifications');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/identifications/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param DocumentIdentification $identification
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(DocumentIdentification $identification, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'DocumentIdentification');

        $command = Edit\Command::fromDocument($identification);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('documents.identifications');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/identifications/edit.html.twig', [
            'form' => $form->createView(),
            'identification' => $identification,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param Request $request
     * @param DocumentRepository $documents
     * @param DocumentIdentificationRepository $identifications
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, Request $request, DocumentRepository $documents, DocumentIdentificationRepository $identifications, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DocumentIdentification');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $identification = $identifications->get($id);
            if ($documents->hasByIdentification($identification) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить идентификационный документ, прикрепленный к документам']);
            } else {
                $em->remove($identification);
                $flusher->flush();
                $data['message'] = 'Идентификационный документ удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param DocumentIdentificationRepository $identifications
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, DocumentIdentificationRepository $identifications, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $identification = $identifications->get($request->query->getInt('id'));
            $identification->hide();
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
     * @param DocumentIdentificationRepository $identifications
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, DocumentIdentificationRepository $identifications, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $identification = $identifications->get($request->query->getInt('id'));
            $identification->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
