<?php


namespace App\Controller\Detail;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExcludeRepository;
use App\Model\EntityNotFoundException;
use App\Model\Detail\UseCase\PartsPrice\Weight;
use App\Model\Detail\UseCase\PartsPrice\Price;
use App\Model\Flusher;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Detail\DetailProviderExcludeFetcher;
use App\ReadModel\Detail\Filter;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\ReadModel\Provider\ProviderFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/parts-price", name="parts.price")
 */
class PartsPriceController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param PartPriceService $partPriceService
     * @param OptRepository $optRepository
     * @param ProviderFetcher $providerFetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws Exception
     */
    public function index(
        Request          $request,
        PartPriceService $partPriceService,
        OptRepository    $optRepository,
        ProviderFetcher  $providerFetcher,
        ManagerSettings  $settings
    ): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PartPrice');

        $filter = new Filter\PartPrice\Filter();

        $form = $this->createForm(Filter\PartPrice\Form::class, $filter);
        $form->handleRequest($request);

        $number = new DetailNumber($filter->number);

        $providers = $providerFetcher->assoc();

        $settings = $settings->get('partsPrice');

        $sort = [
            'sort' => $settings['sort'] ?? 'price1',
            'direction' => $settings['direction'] ?? 'asc',
        ];


        $path = $request->getPathInfo() . '?form[number]=' . ($request->get('form')['number'] ?? '') . '&form[optID]=' . ($request->get('form')['optID'] ?? '');

        $sortColumns = ['price', 'price1', 'profit'];
        $sortOptions = [];
        foreach ($sortColumns as $column) {
            $sortOptions[$column] = [
                'options' => [
                    'href' => $path . '&sort=' . $column . '&direction=' . ($sort['sort'] == $column ? ($sort['direction'] == 'asc' ? 'desc' : 'asc') : $sort['direction']),
                ],
                'sorted' => $sort['sort'] == $column,
                'direction' => $sort['direction']
            ];
        }

        $weight = new Weight\Command();
        $formWeight = $this->createForm(Weight\Form::class, $weight);

        $price = new Price\Command();
        $formPrice = $this->createForm(Price\Form::class, $price);

        if ($number->getValue() != '' && $filter->optID > 0) {
            $opt = $optRepository->get($filter->optID);

            $partPriceService->fullPrice($number, $opt, $sort);

            $arParts = $partPriceService->getArParts();
            $arPartsSort = $partPriceService->getArPartsSort();
            $arCreaterData = $partPriceService->getArCreaterData();

//            dump($arParts);
//            dump($arPartsSort);
        }

        return $this->render('app/detail/partsPrice/index.html.twig', [
            'arPartsSort' => $arPartsSort ?? [],
            'arCreaterData' => $arCreaterData ?? [],
            'providers' => $providers,
            'sortOptions' => $sortOptions,
            'filter' => $form->createView(),
            'formWeight' => $formWeight->createView(),
            'formPrice' => $formPrice->createView(),
            'searchNumber' => $number->getValue(),
        ]);
    }

    /**
     * @Route("/price", name=".price")
     * @param Request $request
     * @param Price\Handler $handler
     * @return Response
     */
    public function price(Request $request, Price\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PartPrice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new Price\Command();
        $form = $this->createForm(Price\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data['message'] = 'Цена изменена';
            try {
                $handler->handle($command);
                $data['reload'] = true;
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
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
     * @Route("/weight", name=".weight")
     * @param Request $request
     * @param Weight\Handler $handler
     * @return Response
     */
    public function weight(Request $request, Weight\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PartPrice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $command = new Weight\Command();
        $form = $this->createForm(Weight\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data['message'] = 'Вес сохранен';
            try {
                $handler->handle($command);
                $data['reload'] = true;
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
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
     * @Route("/delete", name=".delete")
     * @param Request $request
     * @param CreaterRepository $createrRepository
     * @param Flusher $flusher
     * @param PriceUploaderFetcher $priceUploaderFetcher
     * @return Response
     */
    public function delete(Request $request, CreaterRepository $createrRepository, Flusher $flusher, PriceUploaderFetcher $priceUploaderFetcher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'PartPrice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $providerPriceID = $request->get('providerPriceID');
        $createrID = $request->get('createrID');
        $number = $request->get('number');

        $creater = $createrRepository->get($createrID);

        $data = ['code' => 200, 'message' => ''];

        try {
            $priceUploaderFetcher->deletePrice($creater->getTableName(), $providerPriceID, $createrID, $number);
            $flusher->flush();
            $data['message'] = 'Деталь удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/exclude", name=".exclude")
     * @param Request $request
     * @param CreaterRepository $createrRepository
     * @param Flusher $flusher
     * @param DetailProviderExcludeFetcher $detailProviderExcludeFetcher
     * @param DetailProviderExcludeRepository $detailProviderExcludeRepository
     * @return Response
     * @throws Exception
     */
    public function exclude(Request $request, CreaterRepository $createrRepository, Flusher $flusher, DetailProviderExcludeFetcher $detailProviderExcludeFetcher, DetailProviderExcludeRepository $detailProviderExcludeRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'PartPrice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $providerID = $request->get('providerID');
        $createrID = $request->get('createrID');
        $number = $request->get('number');

        $creater = $createrRepository->get($createrID);

        if ($detailProviderExcludeFetcher->hasProviderExclude($number, $createrID, $providerID)) {
            return $this->json(['code' => 401, 'message' => 'Деталь уже исключена']);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $detailProviderExclude = new DetailProviderExclude((new DetailNumber($number)), $creater, $providerID, '');
            $detailProviderExcludeRepository->add($detailProviderExclude);
            $flusher->flush();
            $data['message'] = 'Деталь исключена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}