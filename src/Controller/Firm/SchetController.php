<?php

namespace App\Controller\Firm;

use App\Model\Order\Entity\Good\OrderGood;
use App\Model\User\Entity\User\User;
use App\ReadModel\Firm\SchetFetcher;
use App\Security\Voter\Order\OrderVoter;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Firm\UseCase\Schet\CreateByGood;
use App\Model\Firm\UseCase\Schet\Clear;
use App\Model\Firm\UseCase\Schet\CreateFromNew;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/schet", name="schet")
 */
class SchetController extends AbstractController
{
    /**
     * @Route("/createByGood/{id}", name=".createByGood")
     * @param OrderGood $good
     * @param CreateByGood\Handler $handler
     * @param SchetFetcher $schetFetcher
     * @return Response
     * @throws Exception
     */
    public function createByGood(OrderGood $good, CreateByGood\Handler $handler, SchetFetcher $schetFetcher): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_SCHET, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $handler->handle($good);
            $data['newSchetData'] = $schetFetcher->getSumGoodsNewSchetByUser($good->getOrder()->getUser()->getId());

        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/clear/{id}", name=".clear")
     * @param User $user
     * @param Clear\Handler $handler
     * @return Response
     */
    public function clear(User $user, Clear\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_SCHET, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $handler->handle($user);

        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/createFromNewForm/{id}", name=".createFromNewForm")
     * @param User $user
     * @param SchetFetcher $schetFetcher
     * @return Response
     * @throws Exception
     */
    public function createFromNewForm(User $user, SchetFetcher $schetFetcher): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_SCHET_CREATE, 'Order');

        $command = new CreateFromNew\Command();
        $form = $this->createForm(CreateFromNew\Form::class, $command);

        $newSchetData = $schetFetcher->getSumGoodsNewSchetByUser($user->getId());

        return $this->render('app/orders/goods/schet/form.html.twig', [
            'form' => $form->createView(),
            'newSchetData' => $newSchetData,
            'user' => $user
        ]);
    }

    /**
     * @Route("/createFromNew/{id}", name=".createFromNew")
     * @param User $user
     * @param Request $request
     * @param CreateFromNew\Handler $handler
     * @return Response
     */
    public function createFromNew(User $user, Request $request, CreateFromNew\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_SCHET_CREATE, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $command = new CreateFromNew\Command();
        $form = $this->createForm(CreateFromNew\Form::class, $command);
        $form->handleRequest($request);


        $data = ['code' => 200, 'message' => ''];

        if ($form->isValid()) {
            try {
                $messages = $handler->handle($command, $user);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }
                $data['reload'] = true;

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