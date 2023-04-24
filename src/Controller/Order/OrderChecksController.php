<?php


namespace App\Controller\Order;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Check\Check;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\BalanceHistory\Create;
use App\Model\User\UseCase\BalanceHistory\Edit;
use App\Model\User\UseCase\BalanceHistory\EditFinanceType;
use App\Model\User\UseCase\BalanceHistory\Attach;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\Order\OrderGoodFetcher;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserBalanceHistoryFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Security\Voter\User\UserVoter;
use App\Service\FileUploader;
use DomainException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/checks", name="order.checks")
 */
class OrderChecksController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param UserBalanceHistoryFetcher $fetcher
     * @param ExpenseDocumentFetcher $expenseDocumentFetcher
     * @param ManagerRepository $managerRepository
     * @param Create\Handler $handler
     * @return Response
     * @throws Exception
     */
    public function index(User $user, Request $request, UserBalanceHistoryFetcher $fetcher, ExpenseDocumentFetcher $expenseDocumentFetcher, ManagerRepository $managerRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHECK, 'Order');

        $pagination = $expenseDocumentFetcher->allWithChecks($user, $request->query->getInt('page', 1));

        return $this->render('app/orders/checks/index.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Check $check
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Check $check, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHECK_DELETE, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($check);
            $flusher->flush();
            $this->addFlash('success', 'Чек удален');
            $data['reload'] = true;
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}