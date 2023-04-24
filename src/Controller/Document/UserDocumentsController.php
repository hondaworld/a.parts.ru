<?php


namespace App\Controller\Document;


use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\Document\UseCase\Document\Create;
use App\Model\Document\UseCase\Document\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\User\Entity\User\User;
use App\ReadModel\Document\DocumentFetcher;
use App\Security\Voter\User\UserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users/documents", name="users.documents")
 */
class UserDocumentsController extends AbstractController
{

    /**
     * @Route("/{userID}/", name="")
     * @param User $user
     * @param DocumentFetcher $fetcher
     * @return Response
     */
    public function index(User $user, DocumentFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOCUMENTS, $user);

        $documents = $fetcher->allByUser($user);

        return $this->render('app/documents/users/index.html.twig', [
            'user' => $user,
            'documents' => $documents,
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
        $this->denyAccessUnlessGranted(UserVoter::USER_DOCUMENTS_CHANGE, $user);

        $command = new Create\Command($user);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.documents', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/users/create.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param Document $document
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(User $user, Document $document, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOCUMENTS_CHANGE, $user);

        $command = Edit\Command::fromDocument($document);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.documents', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/documents/users/edit.html.twig', [
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
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(User $user, int $id, Request $request, DocumentRepository $documents, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::USER_DOCUMENTS_CHANGE, $user);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $document = $documents->get($id);
            $em->remove($document);
            $flusher->flush();
            $data['message'] = 'Документ клиента удален';
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{userID}/hide", name=".hide")
     * @param User $user
     * @param Request $request
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(User $user, Request $request, DocumentRepository $documents, Flusher $flusher): Response
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
     * @Route("/{userID}/unHide", name=".unHide")
     * @param User $user
     * @param Request $request
     * @param DocumentRepository $documents
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(User $user, Request $request, DocumentRepository $documents, Flusher $flusher): Response
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