<?php


namespace App\Controller\Provider;


use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\LogPrice\LogPriceRepository;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\Provider\UseCase\Price\Create;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\Security\Voter\Provider\ProviderPriceVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Email\EmailPrice;
use App\Service\ManagerSettings;
use App\Service\PriceUploader;
use DomainException;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers/prices", name="providers.prices")
 */
class ProviderPricesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ProviderPriceFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ProviderPriceFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ProviderPrice');

        $settings = $settings->get('providerPrices');

        $filter = new Filter\Price\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Price\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/providers/prices/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @param CurrencyRepository $currencyRepository
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler, CurrencyRepository $currencyRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ProviderPrice');
        $command = new Create\Command($currencyRepository);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $providerPrice = $handler->handle($command);
                if ($this->isGranted(StandartActionsVoter::SHOW, 'ProviderPrice')) {
                    return $this->redirectToRoute('providers.prices.show', ['id' => $providerPrice->getId()]);
                } else {
                    return $this->redirectToRoute('providers.prices', ['page' => $request->getSession()->get('page/providerPrices') ?: 1]);
                }
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/prices/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param ProviderPrice $providerPrice
     * @param CreaterFetcher $createrFetcher
     * @return Response
     */
    public function show(ProviderPrice $providerPrice, CreaterFetcher $createrFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ProviderPrice');

        $maxCol = max([
            intval($providerPrice->getNum()->getCreater()),
            intval($providerPrice->getNum()->getCreaterAdd()),
            intval($providerPrice->getNum()->getName()),
            intval($providerPrice->getNum()->getNumber()),
            intval($providerPrice->getNum()->getPrice()),
            intval($providerPrice->getNum()->getQuantity()),
            intval($providerPrice->getNum()->getRg()),
        ]);

        return $this->render('app/providers/prices/show.html.twig', [
            'providerPrice' => $providerPrice,
            'creaters' => $createrFetcher->assoc(),
            'maxCol' => $maxCol,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/truncate", name=".truncate")
     * @param ProviderPrice $providerPrice
     * @param CreaterFetcher $createrFetcher
     * @param LogPriceRepository $logPriceRepository
     * @param PriceUploaderFetcher $priceUploaderFetcher
     * @param Flusher $flusher
     * @return Response
     */
    public function truncate(ProviderPrice $providerPrice, CreaterFetcher $createrFetcher, LogPriceRepository $logPriceRepository, PriceUploaderFetcher $priceUploaderFetcher, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ProviderPriceVoter::PROVIDER_PRICE_TRUNCATE, 'ProviderPrice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $priceUploader = new PriceUploader('');
            $priceUploader->truncate($providerPrice, $createrFetcher, $logPriceRepository, $priceUploaderFetcher, $flusher);
            $data['reload'] = true;

        } catch (DomainException $e) {
            return $this->json(['code' => 400, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ProviderPrice $providerPrice
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ProviderPrice $providerPrice, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ProviderPrice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($providerPrice->getOrderGoods()) > 0 || count($providerPrice->getIncomes()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить прайс-лист']);
            }
            $providerPrice->clearChildrenProviderPrices();
            $em->remove($providerPrice);
            $flusher->flush();
            $data['message'] = 'Прайс-лист удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ProviderPriceRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ProviderPriceRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $providerPrice = $repository->get($request->query->getInt('id'));
            $providerPrice->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/unHide", name=".unHide")
     * @param Request $request
     * @param ProviderPriceRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ProviderPriceRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $providerPrice = $repository->get($request->query->getInt('id'));
            $providerPrice->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/emailPrice", name=".emailPrice")
     * @param Imap $imap
     * @param ProviderPriceRepository $providerPriceRepository
     * @return Response
     */
    public function emailPrice(Imap $imap, ProviderPriceRepository $providerPriceRepository): Response
    {
        $emailPrice = new EmailPrice($imap, $providerPriceRepository);
        $emailPrice->saveAttachments($this->getParameter('price_directory'));

        return $this->render('app/home.html.twig', ['news' => []]);
    }
}