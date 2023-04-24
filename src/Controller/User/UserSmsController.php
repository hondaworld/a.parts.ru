<?php


namespace App\Controller\User;


use App\Model\User\Entity\User\User;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserSmsFetcher;
use App\Security\Voter\Order\OrderVoter;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/sms/history", name="order.sms.history")
 */
class UserSmsController extends AbstractController
{

    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param UserSmsFetcher $fetcher
     * @return Response
     * @throws Exception
     */
    public function index(User $user, Request $request, UserSmsFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::USER_SMS_HISTORY, 'Order');

        $pagination = $fetcher->all(
            $user,
            $request->query->getInt('page', 1)
        );

        return $this->render('app/orders/smsHistory/index.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
        ]);
    }
}