<?php


namespace App\Controller\Sklad;


use App\Model\Sklad\UseCase\PriceList\Edit;
use App\Model\Sklad\UseCase\PriceList\Opt;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\PriceList\PriceList;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Provider\ProviderPriceOptFetcher;
use App\ReadModel\Sklad\PriceGroupFetcher;
use App\ReadModel\Sklad\PriceListOptFetcher;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\Sklad\PriceListVoter;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/price-list/price-lists", name="price.list.price.lists")
 */
class PriceListController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name=".edit")
     * @param PriceList $priceList
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(PriceList $priceList, Request $request, Edit\Handler $handler, PriceGroupFetcher $priceGroupFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'PriceList');

        $priceGroups = $priceGroupFetcher->all($priceList->getId());

        $command = Edit\Command::fromEntity($priceList);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('price.list.price.lists.show', ['id' => $priceList->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/sklads/priceLists/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'main',
            'priceGroups' => $priceGroups,
            'priceList' => $priceList
        ]);
    }

    /**
     * @Route("/{id}/opt", name=".opt")
     * @param PriceList $priceList
     * @param Request $request
     * @param Opt\Handler $handler
     * @param PriceListOptFetcher $priceListOptFetcher
     * @param OptFetcher $optFetcher
     * @param PriceGroupFetcher $priceGroupFetcher
     * @return Response
     */
    public function opt(PriceList $priceList, Request $request, Opt\Handler $handler, PriceListOptFetcher $priceListOptFetcher, OptFetcher $optFetcher, PriceGroupFetcher $priceGroupFetcher): Response
    {
        $this->denyAccessUnlessGranted(PriceListVoter::PRICE_LIST_OPT_CHANGE, $priceList);

        $priceGroups = $priceGroupFetcher->all($priceList->getId());

        $opts = $optFetcher->assoc();
        $profits = $priceListOptFetcher->findByPriceList($priceList);

        $command = Opt\Command::fromEntity($priceList, $opts, $profits);

        $form = $this->createForm(Opt\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('price.list.price.lists.show', ['id' => $priceList->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/sklads/priceLists/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'opt',
            'profits' => $profits,
            'opts' => $opts,
            'priceGroups' => $priceGroups,
            'priceList' => $priceList
        ]);
    }
}