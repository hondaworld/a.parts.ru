<?php


namespace App\Controller\Firm;


use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Expense\Entity\SchetFak\SchetFakRepository;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Expense\UseCase\SchetFakKor\Create;
use App\Model\Expense\UseCase\SchetFakKor\SchetFakNumber;
use App\Model\Expense\UseCase\SchetFakKor\IncomeDocumentNumber;
use App\Model\Flusher;
use App\Model\Income\Entity\Document\IncomeDocumentRepository;
use App\ReadModel\Expense\SchetFakFetcher;
use App\ReadModel\Expense\SchetFakKorFetcher;
use App\ReadModel\Firm\SchetFetcher;
use App\ReadModel\Expense\Filter;
use App\ReadModel\Income\IncomeDocumentFetcher;
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
use function foo\func;

/**
 * @Route("/schet-fak-kor", name="schetFakKor")
 */
class SchetFakKorController extends AbstractController
{

    /**
     * @Route("/", name="")
     * @param Request $request
     * @param SchetFakKorFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, SchetFakKorFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'SchetFakKor');

        $settings = $settings->get('schetFakKor');

        $filter = new Filter\SchetFakKor\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\SchetFakKor\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/firms/schetFakKor/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param SchetFakKor $schetFakKor
     * @return Response
     */
    public function show(SchetFakKor $schetFakKor): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'SchetFakKor');

        $goods = $this->getGoods($schetFakKor);

        return $this->render('app/firms/schetFakKor/show.html.twig', [
            'schetFakKor' => $schetFakKor,
            'goods' => $goods,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'SchetFakKor');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $schetFakKor = $handler->handle($command);
                if ($this->isGranted(StandartActionsVoter::SHOW, 'SchetFakKor'))
                    return $this->redirectToRoute('schetFakKor.show', ['id' => $schetFakKor->getId()]);
                else
                    return $this->redirectToRoute('schetFakKor', ['page' => $request->getSession()->get('page/schetFakKor') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/firms/schetFakKor/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/schet-fak", name=".schetFak")
     * @return Response
     */
    public function schetFak(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'SchetFakKor');
        $command = new SchetFakNumber\Command();
        $form = $this->createForm(SchetFakNumber\Form::class, $command);
        return $this->render('app/firms/schetFakKor/schetFak/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/schet-fak-table", name=".schetFakTable")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param SchetFakFetcher $schetFakFetcher
     * @return Response
     */
    public function schetFakTable(Request $request, ValidatorInterface $validator, SchetFakFetcher $schetFakFetcher): Response
    {
        $command = new SchetFakNumber\Command();
        $command->document_num = $request->query->getInt('document_num');

        $errors = $validator->validate($command);

        if ($command->document_num && count($errors) == 0) {
            $all = $schetFakFetcher->allByDocumentNum($command->document_num);
        } else {
            $all = [];
        }

        return $this->render('app/firms/schetFakKor/schetFak/_table.html.twig', [
            'all' => $all
        ]);
    }

    /**
     * @Route("/{id}/schet-fak-add", name=".schetFakAdd")
     * @param Request $request
     * @param SchetFakKor $schetFakKor
     * @param SchetFakRepository $schetFakRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function schetFakAdd(Request $request, SchetFakKor $schetFakKor, SchetFakRepository $schetFakRepository, Flusher $flusher): Response
    {

        try {
            $schetFak = $schetFakRepository->get($request->query->getInt('schetFakID'));
            $schetFakKor->assignSchetFak($schetFak);
            $flusher->flush();
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('schetFakKor.show', ['id' => $schetFakKor->getId()]);
    }

    /**
     * @Route("/income-document", name=".incomeDocument")
     * @return Response
     */
    public function incomeDocument(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'SchetFakKor');
        $command = new IncomeDocumentNumber\Command();
        $form = $this->createForm(IncomeDocumentNumber\Form::class, $command);
        return $this->render('app/firms/schetFakKor/incomeDocument/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/income-document-table", name=".incomeDocumentTable")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param IncomeDocumentFetcher $incomeDocumentFetcher
     * @return Response
     */
    public function incomeDocumentTable(Request $request, ValidatorInterface $validator, IncomeDocumentFetcher $incomeDocumentFetcher): Response
    {
        $command = new SchetFakNumber\Command();
        $command->document_num = $request->query->getInt('document_num');

        $errors = $validator->validate($command);

        if ($command->document_num && count($errors) == 0) {
            $all = $incomeDocumentFetcher->allByDocumentNum($command->document_num);
        } else {
            $all = [];
        }

        return $this->render('app/firms/schetFakKor/incomeDocument/_table.html.twig', [
            'all' => $all
        ]);
    }

    /**
     * @Route("/{id}/income-document-add", name=".incomeDocumentAdd")
     * @param Request $request
     * @param SchetFakKor $schetFakKor
     * @param IncomeDocumentRepository $incomeDocumentRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function incomeDocumentAdd(Request $request, SchetFakKor $schetFakKor, IncomeDocumentRepository $incomeDocumentRepository, Flusher $flusher): Response
    {

        try {
            $schetFak = $incomeDocumentRepository->get($request->query->getInt('incomeDocumentID'));
            $schetFakKor->assignIncomeDocument($schetFak);
            $flusher->flush();
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('schetFakKor.show', ['id' => $schetFakKor->getId()]);
    }

    private function getGoods(SchetFakKor $schetFakKor): array
    {
        $goods = [];
        $incomeDocuments = $schetFakKor->getIncomeDocuments();
        foreach ($incomeDocuments as $incomeDocument) {
            foreach ($incomeDocument->getIncomes() as $income) {
                $good = [
                    'number' => $income->getZapCard()->getNumber()->getValue(),
                    'detailName' => $income->getZapCard()->getDetailName(),
                    'quantity' => $income->getQuantity(),
                    'price' => $income->getPrice(),
                    'quantityReturnAll' => 0,
                    'quantityAll' => 0,
                ];
                foreach ($schetFakKor->getSchetFaks() as $schetFak) {
                    foreach ($schetFak->getExpenseDocument()->getOrderGoods() as $orderGood) {
                        if (
                            $orderGood->getNumber()->isEqual($income->getZapCard()->getNumber()) &&
                            $orderGood->getCreater()->getId() == $income->getZapCard()->getCreater()->getId() &&
                            $income->getPrice() == $orderGood->getDiscountPrice()
                        ) {
                            $good['quantityReturnAll'] += $orderGood->getQuantityReturn();
                            $good['quantityAll'] += $orderGood->getQuantity();
                        }
                    }
                }
                $goods[] = $good;
            }
        }
        usort($goods, function($a, $b) {
            return strcasecmp($a['number'], $b['number']);
        });

        return $goods;
    }

    /**
     * @Route("/{id}/deleteSchetFak", name=".deleteSchetFak")
     * @param SchetFakKor $schetFakKor
     * @param Request $request
     * @param SchetFakRepository $schetFakRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSchetFak(SchetFakKor $schetFakKor, Request $request, SchetFakRepository $schetFakRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'SchetFakKor');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $schetFak = $schetFakRepository->get($request->query->getInt('schetFakID'));
            $schetFakKor->removeSchetFak($schetFak);
            $flusher->flush();
            $data['reload'] = true;

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/deleteIncomeDocument", name=".deleteIncomeDocument")
     * @param SchetFakKor $schetFakKor
     * @param Request $request
     * @param IncomeDocumentRepository $incomeDocumentRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteIncomeDocument(SchetFakKor $schetFakKor, Request $request, IncomeDocumentRepository $incomeDocumentRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'SchetFakKor');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $incomeDocument = $incomeDocumentRepository->get($request->query->getInt('incomeDocumentID'));
            $schetFakKor->removeIncomeDocument($incomeDocument);
            $flusher->flush();
            $data['reload'] = true;

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}