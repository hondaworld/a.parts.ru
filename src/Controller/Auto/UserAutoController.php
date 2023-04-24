<?php


namespace App\Controller\Auto;


use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Auto\Entity\Auto\AutoRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Auto\UseCase\Auto\Create;
use App\Model\Auto\UseCase\Auto\Edit;
use App\Model\User\Entity\User\User;
use App\ReadModel\Auto\AutoFetcher;
use App\ReadModel\Auto\Filter;
use App\Security\Voter\StandartActionsVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users/auto", name="users.auto")
 */
class UserAutoController extends AbstractController
{
    /**
     * @Route("/{userID}/", name="")
     * @param User $user
     * @param Request $request
     * @param AutoFetcher $fetcher
     * @return Response
     */
    public function index(User $user, Request $request, AutoFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Auto');

        $autos = $fetcher->allByUser($user);

        return $this->render('app/auto/users/index.html.twig', [
            'user' => $user,
            'autos' => $autos,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{userID}/create", name=".create")
     * @param User $user
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(User $user, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Auto');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user);
                return $this->redirectToRoute('users.auto', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/users/create.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param Auto $auto
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(User $user, Auto $auto, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Auto');
        $command = Edit\Command::fromEntity($auto);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.auto', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'auto' => $auto
        ]);
    }

    /**
     * @Route("/{userID}/{id}/delete", name=".delete")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param Auto $auto
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(User $user, Auto $auto, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($auto);
            $flusher->flush();
            $data['message'] = 'Автомобиль удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{userID}/hide", name=".hide")
     * @param User $user
     * @param Request $request
     * @param AutoRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(User $user, Request $request, AutoRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $auto = $repository->get($request->query->getInt('id'));
            $auto->hide();
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
     * @param AutoRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(User $user, Request $request, AutoRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $auto = $repository->get($request->query->getInt('id'));
            $auto->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}