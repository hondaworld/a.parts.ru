<?php

namespace App\Controller\Order;

use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Order\Entity\Alert\OrderAlert;
use App\Security\Voter\Order\OrderVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/goods/alerts", name="order.goods.alerts")
 */
class OrderAlertsController extends AbstractController
{
    /**
     * @Route("/{id}/delete", name=".delete")
     * @param OrderAlert $orderAlert
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(OrderAlert $orderAlert, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_DELETE_ALERT, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($orderAlert);
            $flusher->flush();
            $data['message'] = 'Алерт удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
