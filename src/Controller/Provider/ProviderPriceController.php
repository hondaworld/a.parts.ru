<?php


namespace App\Controller\Provider;


use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\UseCase\Price\Edit;
use App\Model\Provider\UseCase\Price\Num;
use App\Model\Provider\UseCase\Price\NumPrice;
use App\Model\Provider\UseCase\Price\Price;
use App\ReadModel\Detail\CreaterFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\PriceUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers/prices", name="providers.prices")
 */
class ProviderPriceController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ProviderPrice $providerPrice
     * @param Request $request
     * @param CreaterFetcher $createrFetcher
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ProviderPrice $providerPrice, Request $request, CreaterFetcher $createrFetcher, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ProviderPrice');

        $command = Edit\Command::fromEntity($providerPrice);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.prices.show', ['id' => $providerPrice->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/prices/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'main',
            'creaters' => $createrFetcher->assoc(),
            'maxCol' => $providerPrice->getNum()->getMaxCol(),
            'providerPrice' => $providerPrice
        ]);
    }

    /**
     * @Route("/{id}/price", name=".price")
     * @param ProviderPrice $providerPrice
     * @param Request $request
     * @param CreaterFetcher $createrFetcher
     * @param Price\Handler $handler
     * @return Response
     */
    public function price(ProviderPrice $providerPrice, Request $request, CreaterFetcher $createrFetcher, Price\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ProviderPrice');

        $command = Price\Command::fromEntity($providerPrice);

        $form = $this->createForm(Price\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.prices.show', ['id' => $providerPrice->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/prices/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'price',
            'creaters' => $createrFetcher->assoc(),
            'maxCol' => $providerPrice->getNum()->getMaxCol(),
            'providerPrice' => $providerPrice
        ]);
    }

    /**
     * @Route("/{id}/num", name=".num")
     * @param ProviderPrice $providerPrice
     * @param Request $request
     * @param CreaterFetcher $createrFetcher
     * @param Num\Handler $handler
     * @return Response
     */
    public function num(ProviderPrice $providerPrice, Request $request, CreaterFetcher $createrFetcher, Num\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ProviderPrice');

        $maxCol = $providerPrice->getNum()->getMaxCol();
        $maxColForm = $maxCol;

        $commandPrice = NumPrice\Command::fromEntity($providerPrice);
        $formPrice = $this->createForm(NumPrice\Form::class, $commandPrice);

        $price = null;
        if (!empty($_FILES)) {
            $formPrice->handleRequest($request);
            if ($formPrice->isSubmitted()) {
                $file = $formPrice->get('file')->getData();
                if ($formPrice->isValid()) {
//                    $i = 1;
//                    $arData = [];
//                    $DataFile = fopen($file->getPathname(), "r");
//                    while (!feof($DataFile)) {
//                        $razd = ';';
//                        $line = fgetcsv($DataFile, 4096, $razd, '"', '"');
//                        $row = [];
//                        if ($line) {
//                            foreach ($line as $item) {
//                                $row[] = mb_convert_encoding($item, 'UTF-8', 'Windows-1251');
//                            }
//                            $maxColForm = max($maxColForm, count($line) - 1);
//                            $arData[] = $row;
//                        }
//                        $i++;
//                        if ($i > 30) break;
//                    }

//                    $arData = [];
//
                    $fileUploader = new PriceUploader($this->getParameter('price_directory') . '/auto');
                    $fileUploader->uploadAndCopy($file);
                    $fileUploader->xlsToCsv($providerPrice);
                    $arData = $fileUploader->getFirstLines($providerPrice);
//                    dump($arData);
                    $fileUploader->delete();


                    $price = json_encode($arData);
                }
            }
        }

        if ($price == null) {
            $maxColForm = $maxCol + ($request->get('form') != null && isset($request->get('form')['maxCols']) ? $request->get('form')['maxCols'] : 3);
        }

        $command = Num\Command::fromEntity($providerPrice, $maxColForm, $price);
        $form = $this->createForm(Num\Form::class, $command);

        if (empty($_FILES)) {
            $form->handleRequest($request);
        }

        if (empty($_FILES) && $form->isSubmitted() && $request->get('form')['isCols'] != 1 && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.prices.show', ['id' => $providerPrice->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/prices/show.html.twig', [
            'form' => $form->createView(),
            'formPrice' => $formPrice->createView(),
            'edit' => 'num',
            'creaters' => $createrFetcher->assoc(),
            'priceCols' => $command->price ? json_decode($command->price, true) : [],
            'maxCol' => $maxCol,
            'maxColForm' => $maxColForm,
            'providerPrice' => $providerPrice
        ]);
    }
}