<?php


namespace App\Controller\Provider;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Provider\Entity\LogInvoice\LogInvoice;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoiceRepository;
use App\Model\Provider\UseCase\LogInvoice\UpdatePrices;
use App\Model\Provider\UseCase\LogInvoice\Update;
use App\Model\Provider\UseCase\LogInvoice\Create;
use App\Model\Provider\Entity\LogInvoiceAll\LogInvoiceAll;
use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Provider\InvoiceFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Email\EmailInvoice;
use App\Service\Email\EmailSender;
use App\Service\ManagerSettings;
use DomainException;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers/invoices", name="providers.invoices")
 */
class InvoicesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param InvoiceFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, InvoiceFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'LogInvoice');

        $settings = $settings->get('logInvoice');

        $filter = new Filter\Invoice\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Invoice\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/providers/invoices/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param LogInvoiceAll $logInvoiceAll
     * @param IncomeStatusFetcher $incomeStatusFetcher
     * @return Response
     */
    public function show(LogInvoiceAll $logInvoiceAll, IncomeStatusFetcher $incomeStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'LogInvoice');

        $sum = [
            'priceZak' => 0,
            'income' => 0,
            'invoice' => 0,
            'quantityIncome' => 0,
            'quantityInvoice' => 0,
        ];

        foreach ($logInvoiceAll->getLogs() as $log) {
            $sum['priceZak'] += $log->getIncome() ? ($log->getIncome()->getPriceZak() * $log->getQuantityIncome()) : 0;
            $sum['income'] += $log->getPriceIncome() * $log->getQuantityIncome();
            $sum['invoice'] += $log->getPriceInvoice() * $log->getQuantityInvoice();
            $sum['quantityIncome'] += $log->getQuantityIncome();
            $sum['quantityInvoice'] += $log->getQuantityInvoice();
        }

        return $this->render('app/providers/invoices/show.html.twig', [
            'logInvoiceAll' => $logInvoiceAll,
            'table_checkable' => true,
            'statuses' => $incomeStatusFetcher->assoc(),
            'sum' => $sum
        ]);
    }

    /**
     * @Route("/updatePrices", name=".updatePrices")
     * @param Request $request
     * @param UpdatePrices\Handler $handler
     * @return Response
     */
    public function updatePrices(Request $request, UpdatePrices\Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'LogInvoice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $command = new UpdatePrices\Command();
        $command->cols = $request->request->get('cols');

        try {
            $messages = $handler->handle($command);

            foreach ($messages as $message) {
                $this->addFlash($message['type'], $message['message']);
            }

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/update", name=".update")
     * @param LogInvoice $logInvoice
     * @param Update\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function update(LogInvoice $logInvoice, Update\Handler $handler, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'LogInvoice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $data += $handler->handle($logInvoice, $manager);
////                $data['reload'] = true;
//
//                $data['inputIdentification'] = [
//                    ['value' => $income->getZapCard()->getNumber()->getValue(), 'name' => 'number'],
//                    ['value' => $income->getZapCard()->getCreater()->getId(), 'name' => 'createrID']
//                ];

//            $data['dataValue'] = [
//                ['value' => $command->weight ?: '', 'name' => 'weight'],
//                ['value' => $command->weightIsReal ? 1 : 0, 'name' => 'weightIsReal'],
//            ];

//            $data['addParentClasses'] = ['text-success'];
            $data['removeParentClasses'] = ['font-weight-bold'];

        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param LogInvoiceAll $logInvoiceAll
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(LogInvoiceAll $logInvoiceAll, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'LogInvoice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($logInvoiceAll);
            $flusher->flush();
            $data['message'] = 'Инвойс удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/emailInvoice", name=".emailInvoice")
     * @param Imap $imap
     * @param ProviderInvoiceRepository $providerInvoiceRepository
     * @param Create\Handler $handler
     * @param EmailSender $emailSender
     * @return Response
     */
    public function emailInvoice(Imap $imap, ProviderInvoiceRepository $providerInvoiceRepository, Create\Handler $handler, EmailSender $emailSender): Response
    {

        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $emailInvoice = new EmailInvoice($imap, $providerInvoiceRepository, $handler, $emailSender);
        $emailInvoice->saveAttachments($this->getParameter('price_directory'));

        return $this->render('app/home.html.twig', ['news' => []]);
    }
}