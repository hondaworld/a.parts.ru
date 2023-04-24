<?php


namespace App\Controller\Provider;


use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoiceRepository;
use App\Model\Provider\UseCase\Invoice\Create;
use App\Model\Provider\UseCase\Invoice\Edit;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Provider\ProviderInvoiceFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\Security\Voter\Provider\ProviderInvoiceVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers/invoice", name="providers.invoice")
 */
class ProviderInvoicesController extends AbstractController
{
    /**
     * @Route("/{providerID}/", name="")
     * @param Provider $provider
     * @param Request $request
     * @param ProviderInvoiceFetcher $fetcher
     * @param IncomeStatusFetcher $incomeStatusFetcher
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(Provider $provider, Request $request, ProviderInvoiceFetcher $fetcher, IncomeStatusFetcher $incomeStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(ProviderInvoiceVoter::PROVIDER_INVOICES, 'Provider');

        $all = $fetcher->all($provider);

        $statuses = $incomeStatusFetcher->assoc();

        foreach ($all as &$item) {
            $statusFrom = explode(',', $item['status_from']);
            $item['status_from_name'] = [];
            foreach ($statusFrom as $status) {
                $item['status_from_name'][] = $statuses[$status];
            }
        }

        return $this->render('app/providers/providerInvoices/index.html.twig', [
            'all' => $all,
            'provider' => $provider,
            'statuses' => $statuses,
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/{providerID}/create", name=".create")
     * @param Provider $provider
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Provider $provider, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ProviderInvoiceVoter::PROVIDER_INVOICES, 'Provider');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $provider);
                return $this->redirectToRoute('providers.invoice', ['providerID' => $provider->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/providerInvoices/create.html.twig', [
            'form' => $form->createView(),
            'provider' => $provider
        ]);
    }

    /**
     * @Route("/{providerID}/{id}/edit", name=".edit")
     * @ParamConverter("provider", options={"id" = "providerID"})
     * @param Provider $provider
     * @param ProviderInvoice $providerInvoice
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Provider $provider, ProviderInvoice $providerInvoice, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ProviderInvoiceVoter::PROVIDER_INVOICES, 'Provider');
        $command = Edit\Command::fromEntity($providerInvoice);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.invoice', ['providerID' => $provider->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/providerInvoices/edit.html.twig', [
            'form' => $form->createView(),
            'provider' => $provider
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param ProviderInvoice $providerInvoice
     * @param ProviderPrice $providerPrice
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ProviderInvoice $providerInvoice, ProviderPrice $providerPrice, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ProviderInvoiceVoter::PROVIDER_INVOICES, 'Provider');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($providerInvoice);
            $flusher->flush();
            $data['message'] = 'Инвойс удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{providerID}/hide", name=".hide")
     * @param Request $request
     * @param ProviderInvoiceRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ProviderInvoiceRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $providerInvoice = $repository->get($request->query->getInt('id'));
            $providerInvoice->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{providerID}/unHide", name=".unHide")
     * @param Request $request
     * @param ProviderInvoiceRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ProviderInvoiceRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $providerInvoice = $repository->get($request->query->getInt('id'));
            $providerInvoice->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}