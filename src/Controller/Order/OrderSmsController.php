<?php

namespace App\Controller\Order;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\User\Entity\User\User;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Firm\SchetFetcher;
use App\Security\Voter\Income\IncomeVoter;
use App\Security\Voter\Order\OrderVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Order\UseCase\Good\SmsWarehouse;
use App\Model\Order\UseCase\Good\SmsPay;
use App\Model\Order\UseCase\Good\ProviderPrice;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/goods/sms", name="order.goods.sms")
 */
class OrderSmsController extends AbstractController
{
    /**
     * @Route("/{id}/warehouseForm", name=".warehouseForm")
     * @param User $user
     * @return Response
     */
    public function warehouseForm(User $user): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_SMS, 'Order');

        $command = new SmsWarehouse\Command();
        $form = $this->createForm(SmsWarehouse\Form::class, $command);

        return $this->render('app/orders/goods/sms/form_warehouse.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/warehouse", name=".warehouse")
     * @param User $user
     * @param Request $request
     * @param ManagerRepository $managerRepository
     * @param SmsWarehouse\Handler $handler
     * @return Response
     */
    public function warehouse(User $user, Request $request, ManagerRepository $managerRepository, SmsWarehouse\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_SMS, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $command = new SmsWarehouse\Command();
        $form = $this->createForm(SmsWarehouse\Form::class, $command);
        $form->handleRequest($request);

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        if ($form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                $data['message'] = 'SMS отправлено на ' . $user->getPhonemob();
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

    /**
     * @Route("/{id}/payForm", name=".payForm")
     * @param User $user
     * @return Response
     */
    public function payForm(User $user): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_SMS, 'Order');

        $command = new SmsPay\Command();
        $form = $this->createForm(SmsPay\Form::class, $command);

        return $this->render('app/orders/goods/sms/form_pay.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/pay", name=".pay")
     * @param User $user
     * @param Request $request
     * @param ManagerRepository $managerRepository
     * @param SmsPay\Handler $handler
     * @return Response
     */
    public function pay(User $user, Request $request, ManagerRepository $managerRepository, SmsPay\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_SMS, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $command = new SmsPay\Command();
        $form = $this->createForm(SmsPay\Form::class, $command);
        $form->handleRequest($request);

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        if ($form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                $data['message'] = 'SMS отправлено на ' . $user->getPhonemob();
                $data['modalClose'] = 'modalForm';
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['messages'][] = $error->getMessage();
            }
        }

        return $this->json($data);
    }
}