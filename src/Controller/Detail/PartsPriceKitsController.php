<?php


namespace App\Controller\Detail;


use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Detail\Entity\Kit\ZapCardKit;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumber;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Order\OrderRepository;
use App\Model\Order\UseCase\Kit\CreateOrder;
use App\Model\Order\UseCase\Good\Create;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Auto\AutoModelFetcher;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/parts-price/kits", name="parts.price.kits")
 */
class PartsPriceKitsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param AutoModelFetcher $autoModelFetcher
     * @return Response
     */
    public function index(AutoModelFetcher $autoModelFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCardKit');

        $models = $autoModelFetcher->findWithKits();

        return $this->render('app/detail/kits/index.html.twig', [
            'models' => $models,
        ]);
    }

    /**
     * @Route("/{id}/model", name=".model")
     * @param AutoModel $autoModel
     * @return Response
     */
    public function model(AutoModel $autoModel): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCardKit');

        return $this->render('app/detail/kits/kits.html.twig', [
            'autoModel' => $autoModel
        ]);
    }

    /**
     * @Route("/{id}/kit", name=".kit")
     * @param ZapCardKit $zapCardKit
     * @param ZapCardRepository $zapCardRepository
     * @param PartPriceService $partPriceService
     * @param OptRepository $optRepository
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     * @throws Exception
     */
    public function kit(ZapCardKit $zapCardKit, ZapCardRepository $zapCardRepository, PartPriceService $partPriceService, OptRepository $optRepository, ProviderPriceFetcher $providerPriceFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCardKit');

        $command = new CreateOrder\Command();
        $form = $this->createForm(CreateOrder\Form::class, $command);
//        $form->handleRequest($request);

//        if ($form->isSubmitted() && $form->isValid()) {
//            try {
////                $handler->handle($command);
////                return $this->redirectToRoute('income', ['page' => $request->getSession()->get('page/income') ?: 1]);
//                $this->addFlash('success', 'Заказ оформлен');
//
//            } catch (DomainException $e) {
//                $this->addFlash('error', $e->getMessage());
//            }
//        }

        $providerPrices = $providerPriceFetcher->allArray();

        $numbers = array_map(function (ZapCardKitNumber $item) {
            return $item->getNumber()->getValue();
        }, $zapCardKit->getNumbers());

        $zapCards = $zapCardRepository->findByNumbers($numbers);

        $parts = [];
        foreach ($zapCardKit->getNumbers() as $item) {
            $number = $item->getNumber()->getValue();
            if (isset($zapCards[$number])) {
                $arParts = $partPriceService->fullPriceForKit($item->getNumber(), $optRepository->get(Opt::DEFAULT_OPT_ID));

                foreach ($arParts as &$part) {
                    if (isset($part['zapSkladID'])) {
                        $part['sklad'] = 1;
                    } elseif ($providerPrices[$part['providerPriceID']]['providerPriceGroupID'] == 2)
                        $part["sklad"] = 2;
                    else
                        $part["sklad"] = 3;
                }

                $arParts = array_values(array_filter($arParts, function ($part) use ($item) {
                    return $part['quantity'] >= $item->getQuantity() || $part['sklad'] == 3;
                }));

                usort($arParts, function ($a, $b) use ($providerPrices) {
                    if ($a['isZamena'] != $b['isZamena']) return $a['isZamena'] <=> $b['isZamena'];
                    if ($a['isOriginal'] != $b['isOriginal']) return $a['isOriginal'] <=> $b['isOriginal'];
                    if ($a['sklad'] != $b['sklad']) return $a['sklad'] <=> $b['sklad'];
                    if ($a['number'] == $b['number'] && isset($a['zapSkladID']) && isset($b['zapSkladID']) && $a['zapSkladID'] != $b['zapSkladID']) return $a['zapSkladID'] <=> $b['zapSkladID'];
                    return $a['price1'] <=> $b['price1'];
                });


                $parts[] = [
                    'id' => $item->getId(),
                    'number' => $number,
                    'creater' => $zapCards[$number] ? $zapCards[$number]->getCreater()->getName() : '',
                    'name' => $zapCards[$number] ? $zapCards[$number]->getDetailName() : '',
                    'quantity' => $item->getQuantity(),
                    'parts' => $arParts
                ];
            }
        }

        return $this->render('app/detail/kits/kit.html.twig', [
            'form' => $form->createView(),
            'zapCardKit' => $zapCardKit,
            'sklads' => $zapSkladFetcher->assoc(),
            'parts' => $parts
        ]);
    }

    /**
     * @Route("/createGood", name=".createGood")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @param UserRepository $userRepository
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function createGood(Request $request, ValidatorInterface $validator, Create\Handler $handler, ManagerRepository $managerRepository, UserRepository $userRepository, OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        $user = $userRepository->get($request->request->getInt('userID'));
        $order = $orderRepository->getWorking($user);

        $command = new Create\Command($order);
        $command->order_add_reasonID = 2;
        $command->zapSkladID = $request->request->get('zapSkladID');
        $command->providerPriceID = $request->request->get('providerPriceID');
        $command->createrID = $request->request->get('createrID');
        $command->number = $request->request->get('number');
        $command->quantity = $request->request->get('quantity');

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                $handler->handle($command, $user, $manager);
//                $data['reload'] = true;
//                $data['redirectToUrl'] = $this->generateUrl('order.goods', ['id' => $user->getId(), 'add' => 1]);
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
}