<?php

namespace App\Controller\Order;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Order\UseCase\ExpenseDocument\Reseller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/goods", name="order.goods")
 */
class OrderResellerController extends AbstractController
{
    /**
     * @Route("/{id}/reseller", name=".reseller")
     * @param ExpenseDocument $expenseDocument
     * @return Response
     */
    public function dateOfServiceForm(ExpenseDocument $expenseDocument): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $command = Reseller\Command::fromEntity($expenseDocument);
        $form = $this->createForm(Reseller\Form::class, $command);

        return $this->render('app/orders/goods/reseller/form.html.twig', [
            'form' => $form->createView(),
            'expenseDocument' => $expenseDocument
        ]);

    }

    /**
     * @Route("/{id}/reseller/update", name=".reseller.update")
     * @param ExpenseDocument $expenseDocument
     * @param Request $request
     * @param Reseller\Handler $handler
     * @return Response
     */
    public function dateOfServiceUpdate(ExpenseDocument $expenseDocument, Request $request, Reseller\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = Reseller\Command::fromEntity($expenseDocument);
        $form = $this->createForm(Reseller\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command);
                $data['dataIdentification'] = [
                    [
                        'value' => $expenseDocument->getId(),
                        'name' => 'expenseDocumentID'
                    ]
                ];
                $data['ident'] = 'reseller';
                $data['value'] = $expenseDocument->getReseller() ? $expenseDocument->getReseller()->getName() : '<span class="text-muted font-italic">не задан</span>';
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