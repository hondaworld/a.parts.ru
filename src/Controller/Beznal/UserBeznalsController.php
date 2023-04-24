<?php


namespace App\Controller\Beznal;


use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Beznal\Entity\Beznal\BeznalRepository;
use App\Model\Beznal\UseCase\Beznal\Create;
use App\Model\Beznal\UseCase\Beznal\Edit;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\ReadModel\Beznal\BankFetcher;
use App\ReadModel\Beznal\BeznalFetcher;
use App\Security\Voter\Manager\ManagerVoter;
use App\Security\Voter\User\UserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users/beznals", name="users.beznals")
 */
class UserBeznalsController extends AbstractController
{

    /**
     * @Route("/{userID}/", name="")
     * @param User $user
     * @param BeznalFetcher $fetcher
     * @return Response
     */
    public function index(User $user, BeznalFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_BEZNALS, $user);

        $beznals = $fetcher->allByUser($user);

        return $this->render('app/beznals/users/index.html.twig', [
            'user' => $user,
            'beznals' => $beznals,
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
        $this->denyAccessUnlessGranted(UserVoter::USER_BEZNALS_CHANGE, $user);

        $command = new Create\Command($user);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.beznals', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/users/create.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param Beznal $beznal
     * @param Request $request
     * @param Edit\Handler $handler
     * @param BankFetcher $bankFetcher
     * @return Response
     */
    public function edit(User $user, Beznal $beznal, Request $request, Edit\Handler $handler, BankFetcher $bankFetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_BEZNALS_CHANGE, $user);

        $command = Edit\Command::fromBeznal($beznal, $bankFetcher);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.beznals', ['userID' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/beznals/users/edit.html.twig', [
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
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(User $user, int $id, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::USER_BEZNALS_CHANGE, $user);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $beznal = $beznals->get($id);
            $beznal->clearCashUsers();
            $beznal->clearGruzUsers();
            $em->remove($beznal);
            $flusher->flush();
            $data['message'] = 'Реквизит клиента удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{userID}/hide", name=".hide")
     * @param User $user
     * @param Request $request
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(User $user, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $beznal = $beznals->get($request->query->getInt('id'));
            if ($beznal->isMain() == 1) {
                $data = ['code' => 500, 'message' => 'Невозможно скрыть основной реквизит'];
            } else {
                $beznal->hide();
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
     * @param BeznalRepository $beznals
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(User $user, Request $request, BeznalRepository $beznals, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $beznal = $beznals->get($request->query->getInt('id'));
            $beznal->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}