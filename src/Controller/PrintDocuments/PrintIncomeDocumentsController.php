<?php

namespace App\Controller\PrintDocuments;

use App\Model\Income\Entity\Document\IncomeDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="")
 */
class PrintIncomeDocumentsController extends AbstractController
{
    /**
     * @Route("/return.php", name="return.php")
     * @param Request $request
     * @param IncomeDocumentRepository $incomeDocumentRepository
     * @return Response
     */
    public function return(
        Request                  $request,
        IncomeDocumentRepository $incomeDocumentRepository
    ): Response
    {
        $incomeDocumentID = $request->query->getInt('id');
        $incomeDocument = $incomeDocumentRepository->get($incomeDocumentID);

        $document_num = $incomeDocument->getDocument()->getDocumentNum();
        $document_date = $incomeDocument->getDateofadded();


        return $this->render('app/income/documentReturn/print.html.twig', [
                'incomeDocument' => $incomeDocument,
                'document_num' => $document_num,
                'document_date' => $document_date
            ]);
    }

    /**
     * @Route("/return1.php", name="return1.php")
     * @param Request $request
     * @param IncomeDocumentRepository $incomeDocumentRepository
     * @return Response
     */
    public function return1(
        Request                  $request,
        IncomeDocumentRepository $incomeDocumentRepository
    ): Response
    {
        $incomeDocumentID = $request->query->getInt('id');
        $incomeDocument = $incomeDocumentRepository->get($incomeDocumentID);

        $document_num = $incomeDocument->getDocument()->getDocumentNum();
        $document_date = $incomeDocument->getDateofadded();


        return $this->render('app/income/documentReturn/print1.html.twig', [
                'incomeDocument' => $incomeDocument,
                'document_num' => $document_num,
                'document_date' => $document_date
            ]);
    }

    /**
     * @Route("/income.php", name="income.php")
     * @param Request $request
     * @param IncomeDocumentRepository $incomeDocumentRepository
     * @return Response
     */
    public function income(
        Request                  $request,
        IncomeDocumentRepository $incomeDocumentRepository
    ): Response
    {
        $incomeDocumentID = $request->query->getInt('id');
        $incomeDocument = $incomeDocumentRepository->get($incomeDocumentID);

        $document_num = $incomeDocument->getDocument()->getDocumentNum();
        $document_date = $incomeDocument->getDateofadded();


        return $this->render('app/income/document/print.html.twig', [
                'incomeDocument' => $incomeDocument,
                'document_num' => $document_num,
                'document_date' => $document_date
            ]);
    }
}
