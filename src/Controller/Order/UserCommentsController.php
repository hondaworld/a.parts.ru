<?php

namespace App\Controller\Order;

use App\Model\EntityNotFoundException;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\Comment\UserComment;
use App\Model\User\Entity\Comment\UserCommentRepository;
use App\Model\User\Entity\User\User;
use App\Model\Order\UseCase\UserComment\Edit;
use App\Model\Order\UseCase\UserComment\Create;
use App\Model\Flusher;
use App\ReadModel\User\UserCommentFetcher;
use App\Security\Voter\Order\OrderVoter;
use DateTime;
use Doctrine\DBAL\Exception;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/user/comments", name="order.user.comments")
 */
class UserCommentsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param UserCommentFetcher $fetcher
     * @param ManagerRepository $managerRepository
     * @param Create\Handler $handler
     * @return Response
     * @throws Exception
     */
    public function index(User $user, Request $request, UserCommentFetcher $fetcher, ManagerRepository $managerRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::USER_COMMENTS, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                return $this->redirectToRoute('order.user.comments', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $arr = $fetcher->all($user);

        $comments = [];

        foreach ($arr as $comment) {
            $year = (new DateTime($comment['dateofadded']))->format('Y');
            $comments[$year][] = $comment;
        }

        return $this->render('app/orders/userComments/index.html.twig', [
            'table_sections' => true,
            'form' => $form->createView(),
            'comments' => $comments,
            'user' => $user
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param UserComment $userComment
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(User $user, UserComment $userComment, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::USER_COMMENTS, 'Order');

        $command = Edit\Command::fromEntity($userComment);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('order.user.comments', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/userComments/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param UserCommentRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, UserCommentRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::USER_COMMENTS, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $template = $repository->get($id);
            $em->remove($template);
            $flusher->flush();
            $data['message'] = 'Комментарий удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/create/form", name=".create.form")
     * @param User $user
     * @return Response
     */
    public function create(User $user): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::USER_COMMENTS, 'Order');

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);

        return $this->render('app/orders/userComments/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);

    }

    /**
     * @Route("/{id}/create/action", name=".create.action")
     * @param User $user
     * @param Request $request
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function createAction(User $user, Request $request, Create\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::USER_COMMENTS, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        $manager = $managerRepository->get($this->getUser()->getId());

        if ($form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                $data['message'] = "Комментарий добавлен";
                $data['modalClose'] = 'modalForm';
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }
}
