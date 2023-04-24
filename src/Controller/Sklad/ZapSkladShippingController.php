<?php

namespace App\Controller\Sklad;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\EntityNotFoundException;
use App\Model\Expense\UseCase\Sklad\Pack;
use App\Model\Expense\UseCase\Sklad\Delete;
use App\Model\Expense\UseCase\Sklad\Send;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Expense\ExpenseShippingFetcher;
use App\ReadModel\Expense\Filter;
use App\ReadModel\Shop\ShopLocationFetcher;
use App\Security\Voter\ExpenseSklad\ExpenseShippingVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/sklads/shipping", name="sklads.shipping")
 */
class ZapSkladShippingController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ExpenseShippingFetcher $fetcher
     * @param ShopLocationFetcher $shopLocationFetcher
     * @param WeightFetcher $weightFetcher
     * @param ZapCardRepository $zapCardRepository
     * @param ManagerSettings $settings
     * @return Response
     * @throws Exception
     */
    public function index(ZapSklad $zapSklad, Request $request, ExpenseShippingFetcher $fetcher, ShopLocationFetcher $shopLocationFetcher, WeightFetcher $weightFetcher, ZapCardRepository $zapCardRepository, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ExpenseSklad');

        $settings = $settings->get('skladsShipping');

        $filter = new Filter\Shipping\Filter();

        $form = $this->createForm(Filter\Shipping\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->allShipping(
            $zapSklad,
            $filter,
            $settings
        );

        $zapCards = [];
        $arZapCards = [];
        $arWeights = [];

        if ($pagination) {
            $items = $pagination->getItems();
            foreach ($items as $item) {
                $arZapCards[] = $item['zapCardID'];
            }

            if ($arZapCards) {
                $zapCards = $zapCardRepository->findByZapCards($arZapCards);
            }

            $locations = $shopLocationFetcher->findByZapCards($arZapCards);
            $expenses = $fetcher->findByZapCards($zapSklad, $arZapCards);

            foreach ($items as &$item) {

                if (!isset($arWeights[$item['zapCardID']])) {
                    $weight = $weightFetcher->allByNumberAndCreater($item['number'], $item['createrID']);
                    $arWeights[$item['zapCardID']] = $weight ? $weight[0] : null;
                }
                $item['weight'] = $arWeights[$item['zapCardID']];

                $quantitySklad = 0;
                $quantitySklad1 = 0;
                $arManagers = [];

                $item['order'] = '';
                foreach ($expenses[$item['zapCardID']] as $expense) {
                    $item['status'] = $expense['status'];

                    if ($expense['providerPriceID']) {
                        $item['location'] .= $expense['provider_price_name'] . ' - ' . $expense['quantity'] . "шт.\n";
                    } else {
                        $quantitySklad += $expense['quantity'];
                    }
                    if ($expense['orderID']) {
                        $item['order'] .= $expense['orderID'] . ' - ' . $expense['quantity'] . "шт.\n";
                    } else {
                        $quantitySklad1 += $expense['quantity'];
                    }
                    if ($expense['managerID'] && !isset($arManagers[$expense['managerID']])) {
                        $arManagers[$expense['managerID']] = $expense['manager_name'];
                    }
                }
                if ($quantitySklad > 0) {
                    $item['location'] .= (isset($locations[$item['zapCardID']][$zapSklad->getId()]) ? $locations[$item['zapCardID']][$zapSklad->getId()]['location'] : 'Склад') . ' - ' . $quantitySklad . "шт.\n";
                }
                if ($quantitySklad1 > 0) {
                    $item['order'] .= 'Склад' . ' - ' . $quantitySklad1 . "шт.\n";
                }
                if (count($arManagers) > 0) {
                    $item['manager'] = implode("\n", $arManagers);
                }
            }

            $sort = $settings['sort'] ?? ExpenseShippingFetcher::DEFAULT_SORT_FIELD_NAME;
            $direction = $settings['direction'] ?? ExpenseShippingFetcher::DEFAULT_SORT_DIRECTION;
            if ($sort == 'location') {
                uasort($items, function ($a, $b) use ($direction) {
                    if ($direction == 'asc') {
                        return $a['location'] <=> $b['location'];
                    } else {
                        return $b['location'] <=> $a['location'];
                    }
                });
            }
            $pagination->setItems($items);
        }

        if ($request->query->get('print') ?? 0) {
            return $this->render('app/sklads/shipping/print.html.twig', [
                'pagination' => $pagination,
                'zapSklad' => $zapSklad,
                'locations' => $locations ?? [],
                'expenses' => $expenses ?? [],
                'zapCards' => $zapCards,
            ]);
        } else {
            return $this->render('app/sklads/shipping/index.html.twig', [
                'table_checkable' => true,
                'pagination' => $pagination,
                'filter' => $form->createView(),
                'zapSklad' => $zapSklad,
                'locations' => $locations ?? [],
                'expenses' => $expenses ?? [],
                'zapCards' => $zapCards,
            ]);
        }
    }

    /**
     * @Route("/{id}/pack", name=".pack")
     * @param ZapSklad $zapSklad
     * @return Response
     */
    public function packForm(ZapSklad $zapSklad): Response
    {
        $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_SHIPPING_PACK, 'ExpenseSklad');

        $command = new Pack\Command();
        $form = $this->createForm(Pack\Form::class, $command);

        return $this->render('app/sklads/shipping/pack/form.html.twig', [
            'form' => $form->createView(),
            'zapSklad' => $zapSklad
        ]);
    }

    /**
     * @Route("/{id}/pack/update", name=".pack.update")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param Pack\Handler $handler
     * @return Response
     */
    public function packUpdate(ZapSklad $zapSklad, Request $request, ValidatorInterface $validator, Pack\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_SHIPPING_PACK, 'ExpenseSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new Pack\Command();
        $command->isDelete = $request->request->get('form')['isDelete'];
        $command->managerID = $request->request->get('form')['managerID'];
        $command->cols = $request->request->get('cols');

        $errors = $validator->validate($command);
        if (count($errors) == 0) {
            try {
                $messages = $handler->handle($command, $zapSklad);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                } else {
                    $data['reload'] = true;
                }


            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($errors as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/deleteSelected", name=".deleteSelected")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param Delete\Handler $handler
     * @return Response
     */
    public function deleteSelected(ZapSklad $zapSklad, Request $request, Delete\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_SHIPPING_DELETE, 'ExpenseSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $id = $request->query->get('id');
            $handler->handle($id, $zapSklad);
            $data['action'] = 'reload';
        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/send", name=".send")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param Send\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function send(ZapSklad $zapSklad, Request $request, Send\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(ExpenseShippingVoter::EXPENSE_SHIPPING_SEND, 'ExpenseSklad');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $handler->handle($zapSklad, $manager);
        } catch (EntityNotFoundException $e) {
            $this->addFlash('error', $e->getMessage());
        }
        return $this->redirectToRoute('sklads.shipping', ['id' => $zapSklad->getId()]);
    }
}
