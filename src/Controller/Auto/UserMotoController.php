<?php


namespace App\Controller\Auto;


use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Auto\Entity\Auto\AutoRepository;
use App\Model\Auto\Entity\Moto\Moto;
use App\Model\Auto\Entity\Moto\MotoRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Auto\UseCase\Moto\Create;
use App\Model\Auto\UseCase\Moto\Edit;
use App\Model\User\Entity\User\User;
use App\ReadModel\Auto\AutoFetcher;
use App\ReadModel\Auto\Filter;
use App\ReadModel\Auto\MotoFetcher;
use App\Security\Voter\StandartActionsVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users/moto", name="users.moto")
 */
class UserMotoController extends AbstractController
{
    /**
     * @Route("/{userID}/", name="")
     * @param User $user
     * @param Request $request
     * @param AutoFetcher $fetcher
     * @return Response
     */
    public function index(User $user, Request $request, MotoFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Auto');

        $motos = $fetcher->allByUser($user);

        return $this->render('app/auto/motoUsers/index.html.twig', [
            'user' => $user,
            'motos' => $motos,
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
                return $this->redirectToRoute('users.moto', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoUsers/create.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param Moto $moto
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(User $user, Moto $moto, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Auto');
        $command = Edit\Command::fromEntity($moto);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.moto', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoUsers/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'moto' => $moto
        ]);
    }

    /**
     * @Route("/{userID}/{id}/delete", name=".delete")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param Moto $moto
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(User $user, Moto $moto, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($moto);
            $flusher->flush();
            $data['message'] = 'Мотоцикл удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{userID}/hide", name=".hide")
     * @param User $user
     * @param Request $request
     * @param MotoRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(User $user, Request $request, MotoRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $moto = $repository->get($request->query->getInt('id'));
            $moto->hide();
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
     * @param MotoRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(User $user, Request $request, MotoRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'Auto');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $moto = $repository->get($request->query->getInt('id'));
            $moto->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}