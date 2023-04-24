<?php

namespace App\Controller\PrintDocuments;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocumentRepository;
use App\Model\Expense\Service\ExpenseDocumentPrintService;
use App\Model\Expense\Service\ExpenseDocumentXlsHelper;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Income\Entity\Document\IncomeDocumentRepository;
use App\Model\Shop\Entity\Discount\DiscountRepository;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Order\OrderGoodFetcher;
use App\Service\Converter\NumberInWordsConverter;
use App\Service\GuidGenerator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="")
 */
class PrintExpenseSkladController extends AbstractController
{
    /**
     * @Route("/perem.php", name="perem.php")
     * @param Request $request
     * @param ExpenseSkladDocumentRepository $expenseSkladDocumentRepository
     * @param WeightFetcher $weightFetcher
     * @return Response
     */
    public function perem(
        Request                        $request,
        ExpenseSkladDocumentRepository $expenseSkladDocumentRepository,
        WeightFetcher                  $weightFetcher
    ): Response
    {
        $expenseSkladDocumentID = $request->query->getInt('id');
        $expenseSkladDocument = $expenseSkladDocumentRepository->get($expenseSkladDocumentID);

        $document_num = $expenseSkladDocument->getDocument()->getDocumentNum();;
        $document_date = $expenseSkladDocument->getDateofadded();

        $weights = [];
        $arWeights = [];
        $expenses = $expenseSkladDocument->getExpenseSklads();
        foreach ($expenses as $expense) {
            $createrID = $expense->getZapCard()->getCreater()->getId();
            $number = $expense->getZapCard()->getNumber()->getValue();
            if (!isset($arWeights[$createrID][$number])) {
                $weight = $weightFetcher->allByNumberAndCreater($expense->getZapCard()->getNumber()->getValue(), $expense->getZapCard()->getCreater()->getId());
                $arWeights[$createrID][$number] = $weight ? $weight[0] : null;
            }
            $weights[$expense->getId()] = $arWeights[$createrID][$number];
        }


        return $this->render('app/sklads/expenses/print.html.twig', [
            'expenseSkladDocument' => $expenseSkladDocument,
            'expenses' => $expenses,
            'weights' => $weights,
            'document_num' => $document_num,
            'document_date' => $document_date
        ]);
    }

}
