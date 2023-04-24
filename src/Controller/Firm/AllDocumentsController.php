<?php

namespace App\Controller\Firm;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Firm\UseCase\AllDocuments\Search;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\Expense\ExpenseSkladDocumentFetcher;
use App\ReadModel\Expense\SchetFakFetcher;
use App\ReadModel\Expense\SchetFakKorFetcher;
use App\ReadModel\Firm\AllDocumentsFetcher;
use App\ReadModel\Firm\SchetFetcher;
use App\ReadModel\Income\IncomeDocumentFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use App\ReadModel\Firm\Filter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/allDocuments", name="allDocuments")
 */
class AllDocumentsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AllDocuments');

        $command = new Search\Command();
        $form = $this->createForm(Search\Form::class, $command);


        return $this->render('app/firms/allDocuments/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/documentType", name=".documentType")
     * @param DocumentType $documentType
     * @param Request $request
     * @param AllDocumentsFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws \Exception
     */
    public function documentType(DocumentType $documentType, Request $request, AllDocumentsFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AllDocuments');

        $command = new Search\Command();
        $command->doc_typeID = $documentType->getId();
        $form = $this->createForm(Search\Form::class, $command);

        $settings = $settings->get('allDocuments');

        $filter = new Filter\AllDocuments\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $formFilter = $this->createForm(Filter\AllDocuments\Form::class, $filter);
        $formFilter->handleRequest($request);

        $pagination = $fetcher->all(
            $documentType,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $template = $this->getTemplate($documentType->getId());

        return $this->render('app/firms/allDocuments/type.html.twig', [
            'pagination' => $pagination,
            'documentType' => $documentType,
            'template' => $template,
            'form' => $form->createView(),
            'filter' => $formFilter->createView()
        ]);
    }

    /**
     * @Route("/documentNum", name=".documentNum")
     * @param Request $request
     * @param SchetFakFetcher $schetFakFetcher
     * @param SchetFakKorFetcher $schetFakKorFetcher
     * @param SchetFetcher $schetFetcher
     * @param ExpenseDocumentFetcher $expenseDocumentFetcher
     * @param IncomeDocumentFetcher $incomeDocumentFetcher
     * @param ExpenseSkladDocumentFetcher $expenseSkladDocumentFetcher
     * @return Response
     */
    public function documentNum(
        Request                     $request,
        SchetFakFetcher             $schetFakFetcher,
        SchetFakKorFetcher          $schetFakKorFetcher,
        SchetFetcher                $schetFetcher,
        ExpenseDocumentFetcher      $expenseDocumentFetcher,
        IncomeDocumentFetcher       $incomeDocumentFetcher,
        ExpenseSkladDocumentFetcher $expenseSkladDocumentFetcher
    ): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AllDocuments');

        $command = new Search\Command();
        $command->document_num = $request->query->getInt('document_num');
        $command->year = $request->query->getInt('year');
        $form = $this->createForm(Search\Form::class, $command);

        try {
            $schetFak = $schetFakFetcher->findByDocumentNumAndYear($command->document_num, $command->year);
            $schetFakKor = $schetFakKorFetcher->findByDocumentNumAndYear($command->document_num, $command->year);
            $schet = $schetFetcher->findByDocumentNumAndYear($command->document_num, $command->year);
            $expenseDocuments = $expenseDocumentFetcher->findByDocumentNumAndYear($command->document_num, $command->year);
            $incomeDocuments = $incomeDocumentFetcher->findByDocumentNumAndYear($command->document_num, $command->year);
            $expenseSkladDocuments = $expenseSkladDocumentFetcher->findByDocumentNumAndYear($command->document_num, $command->year);

            $documents = array_merge(
                $schetFak,
                $schetFakKor,
                $schet,
                $expenseDocuments,
                $incomeDocuments,
                $expenseSkladDocuments
            );
            usort($documents, function($a, $b) {
                return $b['dateofadded'] <=> $a['dateofadded'];
            });

            foreach ($documents as &$document) {
                $document['template'] = $this->getTemplate($document['doc_typeID']);
            }

        } catch (\Exception $e) {
            $documents = [];
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('app/firms/allDocuments/num.html.twig', [
            'documents' => $documents,
            'document_num' => $command->document_num,
            'year' => $command->year,
            'form' => $form->createView()
        ]);
    }

    private function getTemplate(int $doc_typeID): string
    {
        switch ($doc_typeID) {
            case DocumentType::RN:
                $template = 'rn';
                break;
            case DocumentType::TCH:
                $template = 'tch';
                break;
            case DocumentType::SF:
                $template = 'sf';
                break;
            case DocumentType::SFK:
                $template = 'sfk';
                break;
            case DocumentType::S:
                $template = 's';
                break;
            case DocumentType::PN:
                $template = 'pn';
                break;
            case DocumentType::VZ:
                $template = 'vz';
                break;
            case DocumentType::VN:
                $template = 'vn';
                break;
            case DocumentType::WON:
                $template = 'won';
                break;
            case DocumentType::NP:
                $template = 'np';
                break;
            default:
                $template = null;
        }
        return $template;
    }
}